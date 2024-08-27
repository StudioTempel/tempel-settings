<?php

namespace Tempel;

//require_once 'abstract/Page.php';
//require_once 'abstract/Widget.php';

// Views
require_once 'views/general-settings.php';
require_once 'views/widget-settings.php';

// Widgets
require_once 'widgets/status-widget.php';
require_once 'widgets/support-widget.php';
require_once 'widgets/conversion-widget.php';

require_once 'includes/ajax-functions.php';
require_once 'includes/helper.php';
use Tempel\Admin\AJAX_Functions;
use Tempel\Admin\Widgets;
use Tempel\Admin\Pages;

if (!class_exists('Admin')) {
    class Admin
    {
        private $pages = [];
        private $widgets = [];
        
        public function __construct()
        {
            add_action('admin_menu', array($this, 'load_pages'));
            add_action('admin_init', array($this, 'register_plugin_settings'));
            add_action('admin_enqueue_scripts', array($this, 'load_assets'));
            $this->load_widgets();
            new AJAX_Functions();
        }
        
        public function load_widgets()
        {
            if(Helper::sanitize_checkbox_value(Helper::return_option('enable_widgets'))) {
                $this->widgets['status-widget'] = new Widgets\Status_Widget();
                $this->widgets['support-widget'] = new Widgets\Support_Widget();
                
                if (class_exists('GFForms')) {
                    $this->widgets['conversion-widget'] = new Widgets\Conversion_Widget();
                }
            }
        }
        
        public function load_pages()
        {
            $this->pages['tempel-options'] = new Pages\General_Settings(
                'Tempel Settings',
                'Tempel Settings',
                'tempel-settings',
                $this->get_menu_icon(),
                99,
            );
            
            $this->pages['tempel-widget-settings'] = new Pages\Widget_Settings(
                'Widget Settings',
                'Widget Settings',
                'tempel-widget-settings',
                $this->get_menu_icon(),
                1,
                'tempel-settings',
                true
            );
        }
        
        public function register_plugin_settings()
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
        
        public function load_assets()
        {
            $screen = get_current_screen();
            
            $screens = array(
                'toplevel_page_tempel-settings',
                'tempel-settings_page_tempel-widget-settings',
                'tempel-settings_page_tempel-login-settings',
                'tempel-settings_page_tempel-test-settings',
            );
            
            if (in_array($screen->id, $screens)) {
                wp_enqueue_style('tmpl-settings-page', TMPL_PLUGIN_CSS_URL . 'widget-settings.css');
            }
        }
        
        public function get_menu_icon()
        {
            return plugins_url('dist/images/admin-logo.svg', dirname(__FILE__));
        }
    }
}