<?php
namespace Tempel;

require_once 'helper-functions.php';

/**
 * Get the form submissions made in the last 30 days
 *
 * @return array
 */
function get_form_submissions_by_id(): array
{
    $form_ids = get_selected_forms();
    
    if (is_wp_error($form_ids)) {
        return [];
    }
    
    $forms_submissions = [];
    
    foreach ($form_ids as $form_id) {
        $form = \GFAPI::get_form($form_id);
        $form_title = $form['title'];
        $form_link = admin_url('admin.php?page=gf_entries&view=entries&id=' . $form_id);
        
        // only get the entries from the last 30 days
        $end_date = date('Y-m-d H:i:s');
        $start_date = date('Y-m-d H:i:s', strtotime('-30 days'));
        
        $search_criteria = array(
            'status' => 'active',
            'trash' => false,
            'field_filters' => array(
                array(
                    'key' => 'date_created',
                    'value' => $start_date,
                    'operator' => '>=',
                ),
                array(
                    'key' => 'date_created',
                    'value' => $end_date,
                    'operator' => '<=',
                ),
            ),
        );
        
        $form_submissions = \GFAPI::count_entries($form_id, $search_criteria);
        
        
        $forms_submissions[] = [
            'title' => $form_title,
            'link' => $form_link,
            'submissions' => $form_submissions
        ];
    }
    
    return $forms_submissions;
}

/**
 * Get the total number of submissions made in the last 30 days
 *
 * @return mixed
 */
function get_total_submissions(): mixed
{
    if (!class_exists('GFAPI')) {
        return new WP_Error('gfapi_not_found', 'GFAPI class not found.');
    }
    
    $form_ids = get_selected_forms();
    $form_submissions = 0;
    
    foreach ($form_ids as $form_id) {
        $form = \GFAPI::get_form($form_id);
        
        // Get the current date and the date 30 days ago
        $end_date = date('Y-m-d H:i:s');
        $start_date = date('Y-m-d H:i:s', strtotime('-30 days'));
        
        // Set up search criteria
        $search_criteria = array(
            'status' => 'active',
            'trash' => false,
            'field_filters' => array(
                array(
                    'key' => 'date_created',
                    'value' => $start_date,
                    'operator' => '>=',
                ),
                array(
                    'key' => 'date_created',
                    'value' => $end_date,
                    'operator' => '<=',
                ),
            ),
        );
        
        $submissions = \GFAPI::count_entries($form_id, $search_criteria);
        
        $form_submissions += $submissions;
    }
    
    return $form_submissions;
}

/**
 * Get the selected forms from the settings
 *
 * @return mixed
 */
function get_selected_forms(): mixed
{
    $forms = return_option('tmpl_widget_settings', 'conversion_selected_forms');
    
    if (is_wp_error($forms)) {
        return null;
    }
    
    if (!$forms) {
        return [];
    }
    
    if (!is_array($forms)) {
        $forms = [$forms];
    }
    
    return $forms;
}