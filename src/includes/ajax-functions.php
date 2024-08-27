<?php

namespace Tempel\Admin;

class AJAX_Functions
{
    public function __construct()
    {
        add_action('wp_ajax_reset_checkup', array($this, 'reset_checkup'));
        add_action('wp_ajax_reset_update', array($this, 'reset_update'));
        
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
    
    function clear_faq_cache(): void
    {
        $cache_file = TMPL_PLUGIN_CACHE_PATH . 'faq_items_cache.json';
        
        if (file_exists($cache_file)) {
            unlink($cache_file);
        }
    }
}
