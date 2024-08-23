<?php

namespace Tempel\Admin;



// Abstracts
require_once 'abstract/Page.php';
require_once 'abstract/Widget.php';

// Pages
require_once 'pages/general-settings-page.php';
require_once 'pages/widget-settings.php';
require_once 'pages/login-settings.php';

// Includes
require_once 'includes/AjaxCallbacks.php';
require_once 'includes/LoadAdminAssets.php';
require_once 'includes/LoadWidgets.php';

class TempelSettingsAdmin
{
    
    public $pages = array();
    
    public function __construct()
    {
        add_action('admin_menu', array($this, 'add_menu_pages'));
        add_action('admin_init', array($this, 'tempel_register_settings'));
        
        
        new LoadWidgets();
        new LoadAdminAssets();
        new PluginSettingsAjaxCallbacks();
    }
    
    public function add_menu_pages()
    {
        $this->pages['tempel-options'] = new Pages\GeneralSettingsPage(
            'Tempel Settings',
            'Tempel Settings',
            'tempel-settings',
            $this->get_menu_icon(),
            99,
        );
        
        $this->pages['tempel-widget-settings'] = new Pages\WidgetSettingsPage(
            'Widget Settings',
            'Widget Settings',
            'tempel-widget-settings',
            $this->get_menu_icon(),
            1,
            'tempel-settings',
            true
        );
        
    }
    
    public function tempel_register_settings()
    {
        register_setting(
            'tempel_settings',
            'tmpl_settings'
        );
        
        register_setting(
            'tempel_widget_settings',
            'tmpl_widget_settings'
        );
    }
    
    public function get_menu_icon()
    {
        return plugins_url('dist/images/admin-logo.svg', dirname(__FILE__));
    }
}
