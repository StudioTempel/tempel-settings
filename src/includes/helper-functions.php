<?php

namespace Tempel;

/**
 * Returns the option value from the database by option name.
 *
 * @param string $option_name
 * @param string $option_group
 *
 * @return mixed Returns either the option value or false
 *
 * @since 1.0.0
 */
function return_option(string $option_group, string $option_name)
{
    $option = get_option($option_group);
    
    return $option[$option_name] ?? false;
}

/**
 * Sanitize checkbox value to not return 'on' but true.
 *
 * @param string $val
 *
 * @return bool
 *
 * @since 1.0.0
 */
function sanitize_checkbox_value(string $val) : bool
{
    if ($val == 'on') {
        return true;
    } else {
        return false;
    }
}

/**
 * Creates an upload folder subfolder named after the plugin.
 *
 * @return void
 */
function create_upload_folder_if_not_exists() : void
{
    require_once ABSPATH . 'wp-admin/includes/file.php';
    
    $upload_dir = wp_upload_dir();
    $upload_dir = $upload_dir['basedir'];
    $upload_dir = $upload_dir . '/tempel-settings';
    
    if (!file_exists($upload_dir)) {
        mkdir($upload_dir);
    }
}