<?php

namespace Tempel;

// Views
require_once 'views/general-settings.php';
require_once 'views/widget-settings.php';

// Widgets
require_once 'widgets/status-widget.php';
require_once 'widgets/support-widget.php';
require_once 'widgets/conversion-widget.php';
require_once 'widgets/blog-widget.php';

require_once 'includes/ajax-functions.php';
require_once 'includes/helper-functions.php';

if (!class_exists('Admin')) {
    class Admin
    {
        public array $widgets = [];
        public array $pages = [];
        
        /**
         * Constructor
         */
        public function __construct()
        {
            // Hook setting registration
            add_action('admin_init', array($this, 'register_plugin_settings'));
            
            // Hook admin page assets
            add_action('admin_enqueue_scripts', array($this, 'load_assets'));
            
            // Hook the admin pages
            $this->load_pages();
            
            // Load widgets
            $this->load_widgets();
        }
        
        /**
         * Loads the widgets
         *
         * @return void
         */
        public function load_widgets()
        {
            add_action('admin_enqueue_scripts', array($this, 'load_widget_assets'));
            if (sanitize_checkbox_value(return_option('tmpl_widget_settings', 'status_widget_enabled'))) {
                $this->widgets['status-widget'] = new Status_Widget();
            }
            
            if (sanitize_checkbox_value(return_option('tmpl_widget_settings', 'support_widget_enabled'))) {
                $this->widgets['support-widget'] = new Support_Widget();
            }
            
            if (class_exists('GFForms') && sanitize_checkbox_value(return_option('tmpl_widget_settings', 'conversion_widget_enabled'))) {
                $this->widgets['conversion-widget'] = new Conversion_Widget();
            }
        }
        
        function load_widget_assets()
        {
            if (is_admin() && get_current_screen()->id === 'dashboard') {
                wp_enqueue_style('dashboard-widgets', TEMPEL_SETTINGS_ASSET_URL . 'css/dashboard-widgets.css');
                wp_enqueue_script('dashboard-widgets', TEMPEL_SETTINGS_ASSET_URL . 'js/widgets.js', array('jquery'), '1.0', true);
            }
        }
        
        /**
         * Creates the settings pages
         *
         */
        public function load_pages(): void
        {
            $this->pages['tempel-settings'] = new General_Settings(
                'Tempel Settings',
                'Tempel Settings',
                'tempel-settings',
                $this->get_menu_icon(),
                99,
            );
            
            $this->pages['tempel-widget-settings'] = new Widget_Settings(
                'Widget Settings',
                'Widget Settings',
                'tempel-widget-settings',
                $this->get_menu_icon(),
                1,
                'tempel-settings',
                true
            );
        }
        
        public function get_pages()
        {
            return $this->pages;
        }
        
        /**
         * Registers plugin settings
         *
         * @return void
         */
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
        
        /**
         * Loads the assets used on the settings pages
         *
         * @return void
         */
        public function load_assets()
        {
            $screen = get_current_screen();
            
            $screens = array(
                'toplevel_page_tempel-settings',
                'tempel-settings_page_tempel-widget-settings',
                'tempel-settings_page_tempel-login-settings',
            );
            
            if (in_array($screen->id, $screens)) {
                wp_enqueue_style('tmpl-settings-page', TEMPEL_SETTINGS_ASSET_URL . 'css/widget-settings.css');
                wp_enqueue_script('tmpl-settings-page', TEMPEL_SETTINGS_ASSET_URL . 'js/settings.js', array('jquery'), '1.0', true);
            }
        }
        
        /**
         * Returns the menu icon url
         *
         * @return string
         */
        public function get_menu_icon()
        {
            return plugins_url('dist/images/admin-logo.svg', dirname(__FILE__));
        }
    }
}