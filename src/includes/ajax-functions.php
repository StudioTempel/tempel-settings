<?php

namespace Tempel;

function send_sitescan_email() : void
{
    $email = 'job@studiotempel.nl';
    $site = 'https://www.studiotempel.nl';
    $name = 'test';
    
    $subject = 'SiteScan aanvraag';
    $message = 'Beste ' . $name . ',<br><br>';
    $message .= 'Sitescan aanvraag van ' . $site . '.<br><br>';
    
    $headers = array('Content-Type: text/html; charset=UTF-8');
    
    wp_mail($email, $subject, $message, $headers);
    
    echo json_encode(
        array(
            'status' => 'success',
            'email' => $email,
            'site' => $site,
            'name' => $name
        )
    );
}
add_action('wp_ajax_send_sitescan_email', 'send_sitescan_email');

function reset_checkup(): void
{
    $option = get_option('tmpl_widget_settings');
    $option['status_last_checkup_date'] = date('m/Y');
    
    if ($option) {
        update_option('tmpl_widget_settings', $option);
    } else {
        add_option('tmpl_widget_settings', $option);
    }
    
    echo json_encode(
        array(
            'status' => 'success',
            'option' => get_option('tmpl_widget_settings')
        )
    );
}
add_action('wp_ajax_reset_checkup', 'reset_checkup');

function reset_update(): void
{
    $option = get_option('tempel-widget-settings-data');
    $option['last-update-date'] = date('d/m');
    
    if ($option) {
        update_option('tempel-widget-settings-data', $option);
    } else {
        add_option('tempel-widget-settings-data', $option);
    }
    
    echo json_encode(
        array(
            'status' => 'success',
            'option' => get_option('tempel-widget-settings-data')
        )
    );
}
add_action('wp_ajax_reset_update', 'reset_update');

function clear_faq_cache(): void
{
    $cache_file = TEMPEL_SETTINGS_ASSET_DIR . 'cache/faq_items_cache.json';
    
    if (file_exists($cache_file)) {
        unlink($cache_file);
    }
}
add_action('wp_ajax_clear_faq_cache', 'clear_faq_cache');