<?php

namespace Tempel;

require_once 'helper-functions.php';

function get_blogs(): array
{
    $cache_file = wp_upload_dir()['basedir'] . '/tempel-settings/blogs_cache.json';
    
    if (file_exists($cache_file)) {
        $cache_time = filemtime($cache_file);
        $interval = 60 * 60 * 24; // 1 day
        
        if ($cache_time > time() - $interval) {
            $cached_items = json_decode(file_get_contents($cache_file), true);

            return is_array($cached_items) ? $cached_items : array();
        }
    }
    
    return fetch_new_blogs();
}

function fetch_new_blogs()
{
    $response = wp_remote_get('https://studiotempel.nl/wp-json/tmpl/v1/blogs', array('timeout' => 8));
    
    if (!is_array($response) || is_wp_error($response) || wp_remote_retrieve_response_code($response) !== 200) {
        return [];
    }
    
    $body = wp_remote_retrieve_body($response);
    
    $response = json_decode($body, true);

    if (!is_array($response)) {
        return [];
    }
    
    cache_blogs_to_upload_folder($response);
    
    return $response;
}

function cache_blogs_to_upload_folder($blogs)
{
    require_once ABSPATH . 'wp-admin/includes/file.php';
    
    create_upload_folder_if_not_exists();
    
    $blogs = wp_json_encode($blogs);
    if (!$blogs) {
        return;
    }

    $cache_file = wp_upload_dir()['basedir'] . '/tempel-settings/blogs_cache.json';
    
    file_put_contents($cache_file, $blogs, LOCK_EX);
}
