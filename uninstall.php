<?php

/**
 * Exit unless the plugin is being uninstalled.
 */
if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

/**
 * Delete the uploads folder made by the plugin.
 */
//function delete_uploads_folder()
//{
//    require_once ABSPATH . 'wp-admin/includes/file.php';
//
//    $upload_dir = wp_upload_dir();
//    $upload_dir = $upload_dir['basedir'];
//    $upload_dir = $upload_dir . '/tempel-settings';
//
//    if (file_exists($upload_dir)) {
//        rmdir($upload_dir);
//    }
//}
//
//delete_uploads_folder();

/**
 * Delete plugin options
 */
//function delete_plugin_options()
//{
//    delete_option('tmpl_settings');
//    delete_option('tmpl_widget_settings');
//}
//
//delete_plugin_options();