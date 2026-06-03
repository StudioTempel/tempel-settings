<?php

namespace Tempel;

require_once 'helper-functions.php';

function get_faq_link()
{
    $faq_link = return_option('tmpl_widget_settings', 'support_faq_link');
    
    if (empty($faq_link) || !filter_var($faq_link, FILTER_VALIDATE_URL)) {
        return 'https://studiotempel.nl/faq';
    }
    
    return $faq_link;
}

/**
 * Get faq items
 * Returns faq items from cache or fetches new items if cache is older than 1 day
 *
 * @return array
 */
function get_faq_items(): array
{
    $cache_file = wp_upload_dir()['basedir'] . '/tempel-settings/faq_items_cache.json';
    
    if (file_exists($cache_file)) {
        $cache_time = filemtime($cache_file);
        $interval = 60 * 60 * 24; // 1 day
        
        if ($cache_time > time() - $interval) {
            $cached_items = json_decode(file_get_contents($cache_file), true);

            return is_array($cached_items) ? $cached_items : array();
        }
    }
    
    return fetch_new_faq_items();
}

function fetch_new_faq_items()
{
    $response = wp_remote_get('https://studiotempel.nl/wp-json/tmpl/v1/faq?show_in_widget_value=1', array('timeout' => 8));
    
    if (!is_array($response) || is_wp_error($response) || wp_remote_retrieve_response_code($response) !== 200) {
        return [];
    }
    
    $body = wp_remote_retrieve_body($response);
    
    $response = json_decode($body, true);

    if (!is_array($response)) {
        return [];
    }
    
    cache_faq_items_to_upload_folder($response);
    
    return $response;
}

function cache_faq_items_to_upload_folder($faq_items)
{
    require_once ABSPATH . 'wp-admin/includes/file.php';
    
    create_upload_folder_if_not_exists();
    
    $faq_items = wp_json_encode($faq_items);
    if (!$faq_items) {
        return;
    }

    $cache_file = wp_upload_dir()['basedir'] . '/tempel-settings/faq_items_cache.json';
    
    file_put_contents($cache_file, $faq_items, LOCK_EX);
}
