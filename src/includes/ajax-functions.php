<?php

namespace Tempel;

//function send_sitescan_email() : void
//{
//    $email = 'job@studiotempel.nl';
//    $site = 'https://www.studiotempel.nl';
//    $name = 'test';
//
//    $subject = 'SiteScan aanvraag';
//    $message = 'Beste ' . $name . ',<br><br>';
//    $message .= 'Sitescan aanvraag van ' . $site . '.<br><br>';
//
//    $headers = array('Content-Type: text/html; charset=UTF-8');
//
//    wp_mail($email, $subject, $message, $headers);
//
//    echo json_encode(
//        array(
//            'status' => 'success',
//            'email' => $email,
//            'site' => $site,
//            'name' => $name
//        )
//    );
//}
//add_action('wp_ajax_send_sitescan_email', 'send_sitescan_email');

function reset_checkup(): void
{
    check_ajax_referer('tmpl_widget_settings_action', 'nonce');

    if (!current_user_can('manage_options')) {
        wp_send_json_error(array('message' => __('You are not allowed to reset the checkup date.', 'tempel-settings')), 403);
    }

    $option = get_option('tmpl_widget_settings');
    $option = is_array($option) ? $option : array();
    $option['status_last_checkup_date'] = current_time('m/Y');
    
    if (get_option('tmpl_widget_settings') !== false) {
        update_option('tmpl_widget_settings', $option);
    } else {
        add_option('tmpl_widget_settings', $option);
    }
    
    wp_send_json_success(
        array(
            'status_last_checkup_date' => $option['status_last_checkup_date'],
        )
    );
}
add_action('wp_ajax_reset_checkup', __NAMESPACE__ . '\reset_checkup');

function reset_update(): void
{
    check_ajax_referer('tmpl_widget_settings_action', 'nonce');

    if (!current_user_can('manage_options')) {
        wp_send_json_error(array('message' => __('You are not allowed to reset the update date.', 'tempel-settings')), 403);
    }

    $option = get_option('tempel-widget-settings-data');
    $option = is_array($option) ? $option : array();
    $option['last-update-date'] = current_time('d/m');
    
    if (get_option('tempel-widget-settings-data') !== false) {
        update_option('tempel-widget-settings-data', $option);
    } else {
        add_option('tempel-widget-settings-data', $option);
    }
    
    wp_send_json_success(
        array(
            'last-update-date' => $option['last-update-date'],
        )
    );
}
add_action('wp_ajax_reset_update', __NAMESPACE__ . '\reset_update');

function clear_faq_cache(): void
{
    check_ajax_referer('tmpl_widget_settings_action', 'nonce');

    if (!current_user_can('manage_options')) {
        wp_send_json_error(array('message' => __('You are not allowed to clear the FAQ cache.', 'tempel-settings')), 403);
    }

    $cache_file = wp_upload_dir()['basedir'] . '/tempel-settings/faq_items_cache.json';
    
    if (file_exists($cache_file)) {
        wp_delete_file($cache_file);
    }

    wp_send_json_success();
}
add_action('wp_ajax_clear_faq_cache', __NAMESPACE__ . '\clear_faq_cache');
