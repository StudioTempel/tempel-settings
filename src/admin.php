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
require_once 'widgets/analytics-widget.php';
require_once 'widgets/post-type-count-widget.php';


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
            $this->load_ajax_functions();
            // Hook setting registration
            add_action('admin_init', array($this, 'register_plugin_settings'));
            
            // Hook admin page assets
            add_action('admin_enqueue_scripts', array($this, 'load_assets'));
            
            // Hook the admin pages
            $this->load_pages();
            
            // Load widgets
            $this->load_widgets();
        }
        
        function load_ajax_functions()
        {
            require_once 'includes/ajax-functions.php';
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

            if (sanitize_checkbox_value(return_option('tmpl_widget_settings', 'analytics_widget_enabled')) && $this->is_site_kit_active()) {
                $this->widgets['analytics-widget'] = new Analytics_Widget();
            }

            if (sanitize_checkbox_value(return_option('tmpl_widget_settings', 'post_type_count_widget_enabled'))) {
                $this->widgets['post-type-count-widget'] = new Post_Type_Count_Widget();
            }
        }

        public function is_site_kit_active(): bool
        {
            if (class_exists('Google\Site_Kit\Plugin')) {
                return true;
            }

            if (!function_exists('is_plugin_active')) {
                require_once ABSPATH . 'wp-admin/includes/plugin.php';
            }

            return \is_plugin_active('google-site-kit/google-site-kit.php') || \is_plugin_active_for_network('google-site-kit/google-site-kit.php');
        }
        
        function load_widget_assets()
        {
            if (is_admin() && get_current_screen()->id === 'dashboard') {
                wp_enqueue_style('dashboard-widgets', TEMPEL_SETTINGS_ASSET_URL . 'css/dashboard-widgets.css');
                wp_enqueue_style('analytics-widget', TEMPEL_SETTINGS_ASSET_URL . 'css/analytics-widget.css', array('dashboard-widgets'), filemtime(TEMPEL_SETTINGS_ASSET_DIR . 'css/analytics-widget.css'));
                wp_enqueue_style('post-type-count-widget', TEMPEL_SETTINGS_ASSET_URL . 'css/post-type-count-widget.css', array('dashboard-widgets'), filemtime(TEMPEL_SETTINGS_ASSET_DIR . 'css/post-type-count-widget.css'));
                wp_enqueue_script('dashboard-widgets', TEMPEL_SETTINGS_ASSET_URL . 'js/widgets.js', array('jquery'), filemtime(TEMPEL_SETTINGS_ASSET_DIR . 'js/widgets.js'), true);
                wp_localize_script('dashboard-widgets', 'tempelAnalyticsWidget', array(
                    'endpoint' => rest_url('google-site-kit/v1/modules/analytics-4/data/report'),
                    'nonce' => wp_create_nonce('wp_rest'),
                    'messages' => array(
                        'unavailable' => __('Connect Google Site Kit and Analytics to show visitors.', 'tempel-settings'),
                        'error' => __('Visitors could not be retrieved.', 'tempel-settings'),
                    ),
                ));
            }
        }
        
        /**
         * Creates the settings pages
         *
         */
        public function load_pages(): void
        {
            $this->pages['tempel-settings'] = new General_Settings(
                __('General', 'tempel-settings'),
                __('Tempel Settings', 'tempel-settings'),
                'tempel-settings',
                $this->get_menu_icon(),
                99,
            );
            
            $this->pages['tempel-widget-settings'] = new Widget_Settings(
                __('Widget', 'tempel-settings'),
                __('Widgets', 'tempel-settings'),
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
                'tmpl_settings',
                array(
                    'sanitize_callback' => array($this, 'sanitize_general_settings'),
                )
            );
            
            register_setting(
                'tempel_widget_settings',
                'tmpl_widget_settings'
            );
        }

        public function sanitize_general_settings($input): array
        {
            $input = is_array($input) ? $input : array();
            $current = get_option('tmpl_settings', array());
            $output = is_array($current) ? $current : array();

            $checkboxes = array(
                'enable_branding',
                'disable_comments',
                'disable_default_pt',
                'hide_dashboard_widgets',
                'svg_support',
                'taxonomy_order',
                'gf_bag_address_enabled',
                'magic_login_enabled',
                'magic_login_allow_admins',
            );

            foreach ($checkboxes as $key) {
                $output[$key] = isset($input[$key]) && $input[$key] === 'on' ? 'on' : '';
            }

            $output['gf_bag_address_api_key'] = isset($input['gf_bag_address_api_key']) ? sanitize_text_field($input['gf_bag_address_api_key']) : '';
            $output['gf_bag_address_endpoint'] = isset($input['gf_bag_address_endpoint']) ? esc_url_raw($input['gf_bag_address_endpoint']) : '';
            $output['gf_bag_address_timeout'] = isset($input['gf_bag_address_timeout']) ? (string) max(1, min(30, absint($input['gf_bag_address_timeout']))) : '8';
            $output['magic_login_expiration'] = isset($input['magic_login_expiration']) ? (string) max(1, min(60, absint($input['magic_login_expiration']))) : '10';

            return $output;
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
                'toplevel_page_tempel-widget-settings',
                'tempel-settings_page_tempel-widget-settings',
                'tempel-settings_page_tempel-login-settings',
            );
            
            if (in_array($screen->id, $screens)) {
                wp_enqueue_style('tmpl-settings-page', TEMPEL_SETTINGS_ASSET_URL . 'css/widget-settings.css');
                wp_enqueue_style('tmpl-settings-overrides', TEMPEL_SETTINGS_ASSET_URL . 'css/settings-overrides.css', array('tmpl-settings-page'));
                wp_enqueue_script('tmpl-settings-page', TEMPEL_SETTINGS_ASSET_URL . 'js/settings.js', array('jquery'), filemtime(TEMPEL_SETTINGS_ASSET_DIR . 'js/settings.js'), true);
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
