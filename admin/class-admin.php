<?php

namespace Tempel\Admin;

// Pages
require_once 'pages/general-settings-page.php';
require_once 'pages/widget-settings.php';
require_once 'pages/login-settings.php';

// Includes
require_once 'includes/ajax-callbacks.php';
require_once 'includes/load-admin-assets.php';

use Tempel\Admin\Pages;

class Admin
{
    
    public $pages = array();
    
    
    public function __construct()
    {
        add_action('admin_menu', array($this, 'add_menu_pages'));
        add_action('admin_init', array($this, 'tempel_register_settings'));
        

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
        
//        $this->pages['tempel-login-settings'] = new Pages\LoginPageSettings(
//            'Login Page Settings',
//            'Login Page Settings',
//            'tempel-login-settings',
//            $this->get_menu_icon(),
//            2,
//            'tempel-settings',
//            true
//        );
    }
    
    public function tempel_register_settings()
    {
        register_setting(
            'tempel-settings',
            'tempel-settings-data'
        );
        
        register_setting(
            'tempel-widget-settings',
            'tempel-widget-settings-data'
        );
        
//        register_setting(
//            'tempel-login-page-settings',
//            'tempel-login-page-settings-data'
//        );
    }
    
    public function get_menu_icon()
    {
        return plugins_url('assets/images/admin-logo.svg', dirname(__FILE__));
    }
}
