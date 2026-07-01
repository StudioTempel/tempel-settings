<?php
namespace Tempel;

require_once TEMPEL_SETTINGS_DIR . 'src/includes/helper-functions.php';

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

    if (!is_array($form_ids)) {
        return [];
    }
    
    $forms_submissions = [];
    
    foreach ($form_ids as $form_id) {
        $form_id = absint($form_id);
        $form = \GFAPI::get_form($form_id);

        if (!$form || is_wp_error($form)) {
            continue;
        }

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

function get_conversion_items(): array
{
    $items = class_exists('GFAPI') ? get_form_submissions_by_id() : array();

    if (include_woocommerce_orders_in_conversions()) {
        $items[] = array(
            'title' => __('Orders', 'tempel-settings'),
            'link' => get_woocommerce_orders_admin_link(),
            'submissions' => get_woocommerce_order_conversions_count(),
        );
    }

    if (include_post_type_in_conversions()) {
        $post_type = get_conversion_post_type();
        $items[] = array(
            'title' => get_conversion_post_type_label($post_type),
            'link' => admin_url('edit.php?post_type=' . $post_type),
            'submissions' => get_post_type_conversions_count($post_type),
        );
    }

    return $items;
}

/**
 * Get the total number of submissions made in the last 30 days
 *
 * @return mixed
 */
function get_total_submissions(): mixed
{
    $form_ids = get_selected_forms();
    $form_submissions = 0;

    if (!is_array($form_ids)) {
        $form_ids = array();
    }
    
    if (class_exists('GFAPI')) {
        foreach ($form_ids as $form_id) {
            $form_id = absint($form_id);
            $form = \GFAPI::get_form($form_id);

            if (!$form || is_wp_error($form)) {
                continue;
            }

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
    }

    if (include_woocommerce_orders_in_conversions()) {
        $form_submissions += get_woocommerce_order_conversions_count();
    }

    if (include_post_type_in_conversions()) {
        $form_submissions += get_post_type_conversions_count(get_conversion_post_type());
    }
    
    return $form_submissions;
}

function include_woocommerce_orders_in_conversions(): bool
{
    if (!function_exists('wc_get_orders')) {
        return false;
    }

    return return_option('tmpl_widget_settings', 'conversion_include_woocommerce_orders') === 'on';
}

function get_woocommerce_order_conversions_count(): int
{
    if (!function_exists('wc_get_orders')) {
        return 0;
    }

    $statuses = function_exists('wc_get_order_statuses') ? array_keys(wc_get_order_statuses()) : array();
    $orders = wc_get_orders(array(
        'limit' => 1,
        'paginate' => true,
        'return' => 'ids',
        'status' => $statuses,
        'date_created' => '>' . strtotime('-30 days', current_time('timestamp')),
    ));

    return isset($orders->total) ? (int) $orders->total : 0;
}

function get_woocommerce_orders_admin_link(): string
{
    if (
        class_exists('Automattic\WooCommerce\Utilities\OrderUtil') &&
        \Automattic\WooCommerce\Utilities\OrderUtil::custom_orders_table_usage_is_enabled()
    ) {
        return admin_url('admin.php?page=wc-orders');
    }

    return admin_url('edit.php?post_type=shop_order');
}

function include_post_type_in_conversions(): bool
{
    $post_type = get_conversion_post_type();

    return return_option('tmpl_widget_settings', 'conversion_include_post_type') === 'on' && $post_type && post_type_exists($post_type);
}

function get_conversion_post_type(): string
{
    $post_type = return_option('tmpl_widget_settings', 'post_type_count_post_type');

    return $post_type ? sanitize_key($post_type) : '';
}

function get_conversion_post_type_statuses(): array
{
    $statuses = return_option('tmpl_widget_settings', 'post_type_count_statuses');

    if (!$statuses) {
        $post_stati = get_post_stati(array(), 'names');

        return array_diff($post_stati, array('trash', 'auto-draft'));
    }

    $statuses = explode(',', $statuses);
    $statuses = array_map('trim', $statuses);
    $statuses = array_map('sanitize_key', $statuses);
    $statuses = array_filter($statuses);

    return $statuses ?: array('publish');
}

function get_post_type_conversions_count(string $post_type): int
{
    if (!$post_type || !post_type_exists($post_type)) {
        return 0;
    }

    $query = new \WP_Query(array(
        'post_type' => $post_type,
        'post_status' => get_conversion_post_type_statuses(),
        'posts_per_page' => 1,
        'fields' => 'ids',
        'date_query' => array(
            array(
                'after' => '30 days ago',
                'inclusive' => true,
            ),
        ),
    ));

    return (int) $query->found_posts;
}

function get_conversion_post_type_label(string $post_type): string
{
    $post_type_object = get_post_type_object($post_type);

    if (!$post_type_object) {
        return __('Post type', 'tempel-settings');
    }

    return $post_type_object->labels->name ?: $post_type_object->label;
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
