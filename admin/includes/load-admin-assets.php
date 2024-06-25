<?php

namespace Tempel\Admin;

class LoadAdminAssets
{
    public function __construct()
    {
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_settings_scripts'));
    }
    
    public function enqueue_admin_settings_scripts()
    {
        $screen = get_current_screen();
        
        $screens = array(
            'toplevel_page_tempel-settings',
            'tempel-settings_page_tempel-widget-settings',
            'tempel-settings_page_tempel-login-settings',
        );
        
        if (in_array($screen->id, $screens)) {
            wp_enqueue_style('tmpl-settings-page', TMPL_PLUGIN_CSS_URL . 'widget-settings.css');
        }
    }
}