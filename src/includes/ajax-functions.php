<?php

namespace Tempel\Admin;

class AJAX_Functions
{
    public function __construct()
    {
        add_action('wp_ajax_reset_checkup', array($this, 'reset_checkup'));
        add_action('wp_ajax_reset_update', array($this, 'reset_update'));
        add_action('wp_ajax_save_login_image_to_plugin_upload_folder', array($this, 'save_login_image_to_plugin_upload_folder'));
        
        add_action('wp_ajax_clear_faq_cache', array($this, 'clear_faq_cache'));
    }
    
    public function reset_checkup(): void
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
    
    public function reset_update(): void
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
    
    public function save_login_image_to_plugin_upload_folder(): void
    {
        // get the file from ajax
        $file = $_FILES['pondFile'];
        $upload_path = TMPL_PLUGIN_UPLOAD_PATH;
        
        
        if (!$upload_path) {
            echo json_encode([
                'status' => 'error',
                'message' => 'Upload path not found'
            ]);
        }
        
        if ($file == '' || empty($file)) {
            echo json_encode([
                'status' => 'error',
                'message' => 'No file found',
                'file' => $file
            ]);
        }
        
        // upload the file to the upload folder
        $file_name = $file['name'];
        $file_tmp_name = $file['tmp_name'];
        
        $file_path = $upload_path . $file_name;
        $file_url = TMPL_PLUGIN_UPLOAD_URL . $file_name;
        
        $this->clear_uploads_folder();
        move_uploaded_file($file_tmp_name, $file_path);
        
        
        if (file_exists($file_path)) {
            
            // Get the option
            $option = get_option('tempel-login-page-settings-data');
            
            // If the option is a string (when empty for example), convert it to an array
            $option = wp_parse_args($option, array());
            
            // Update the option with the new image path
            $option['login-bg-image'] = $file_url;
            
            update_option('tempel-login-page-settings-data', $option);
            
            echo json_encode([
                'status' => 'success',
                'message' => 'File uploaded successfully',
                'file_path' => $file_path,
                'file_url' => $file_url,
                'option' => $option,
            ]);
        } else {
            echo json_encode([
                'status' => 'error',
                'message' => 'File upload failed'
            ]);
        }
        
        exit();
    }
    
    function clear_uploads_folder(): void
    {
        $upload_path = TMPL_PLUGIN_UPLOAD_PATH;
        
        $files = glob($upload_path . '*');
        
        foreach ($files as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }
    }
    
    function clear_cache_folder(): void
    {
        $cache_path = TMPL_PLUGIN_CACHE_PATH;
        
        $files = glob($cache_path . '*');
        
        foreach ($files as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }
    }
    
    function clear_faq_cache(): void
    {
        $cache_file = TMPL_PLUGIN_CACHE_PATH . 'faq_items_cache.json';
        
        if (file_exists($cache_file)) {
            unlink($cache_file);
        }
    }
}
