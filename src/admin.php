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
            
            if (sanitize_checkbox_value(return_option('tmpl_widget_settings', 'conversion_widget_enabled'))) {
                $this->widgets['conversion-widget'] = new Conversion_Widget();
            }

            if (sanitize_checkbox_value(return_option('tmpl_widget_settings', 'analytics_widget_enabled')) && $this->is_site_kit_active()) {
                $this->widgets['analytics-widget'] = new Analytics_Widget();
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
                wp_enqueue_script('dashboard-widgets', TEMPEL_SETTINGS_ASSET_URL . 'js/widgets.js', array('jquery'), filemtime(TEMPEL_SETTINGS_ASSET_DIR . 'js/widgets.js'), true);
                wp_localize_script('dashboard-widgets', 'tempelAnalyticsWidget', array(
                    'endpoint' => rest_url('google-site-kit/v1/modules/analytics-4/data/report'),
                    'nonce' => wp_create_nonce('wp_rest'),
                    'messages' => array(
                        'unavailable' => __('Connect Google Site Kit and Analytics to show visitors.', 'tempel-settings'),
                        'error' => __('Visitors could not be retrieved.', 'tempel-settings'),
                    ),
                ));
                wp_localize_script('dashboard-widgets', 'tempelSupportActions', array(
                    'ajaxUrl' => admin_url('admin-ajax.php'),
                    'nonce' => wp_create_nonce('tempel_support_actions'),
                    'hasCacheAction' => \Tempel\has_supported_cache_plugin(),
                    'messages' => array(
                        'cacheConfirm' => __('Clear the site cache?', 'tempel-settings'),
                        'cacheSuccess' => __('Cache cleared.', 'tempel-settings'),
                        'cacheError' => __('Cache could not be cleared.', 'tempel-settings'),
                        'mailConfirm' => __('Send a test email to your account?', 'tempel-settings'),
                        'mailSuccess' => __('Test email sent.', 'tempel-settings'),
                        'mailError' => __('Test email could not be sent.', 'tempel-settings'),
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
                'tmpl_widget_settings',
                array(
                    'sanitize_callback' => array($this, 'sanitize_widget_settings'),
                )
            );
        }

        public function sanitize_widget_settings($input): array
        {
            $input = is_array($input) ? $input : array();
            $current = get_option('tmpl_widget_settings', array());
            $output = is_array($current) ? $current : array();

            $checkboxes = array(
                'analytics_widget_enabled',
                'conversion_widget_enabled',
                'conversion_include_woocommerce_orders',
                'conversion_include_post_type',
                'status_widget_enabled',
                'status_show_service_contract_tier',
                'status_service_contract_upgradable',
                'support_widget_enabled',
            );

            foreach ($checkboxes as $key) {
                $output[$key] = isset($input[$key]) && $input[$key] === 'on' ? 'on' : '';
            }

            $days = array('monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday');
            $safeupdate_day = isset($input['status_safeupdate_day']) ? sanitize_key($input['status_safeupdate_day']) : '';
            $output['status_safeupdate_day'] = in_array($safeupdate_day, $days, true) ? $safeupdate_day : '';

            $output['status_backup_interval'] = isset($input['status_backup_interval']) && preg_match('/^\d{2}:\d{2}$/', $input['status_backup_interval'])
                ? sanitize_text_field($input['status_backup_interval'])
                : '';

            $output['status_last_checkup_date'] = isset($input['status_last_checkup_date']) && preg_match('/^\d{2}\/\d{4}$/', $input['status_last_checkup_date'])
                ? sanitize_text_field($input['status_last_checkup_date'])
                : current_time('m/Y');

            $output['status_service_contract_tier'] = isset($input['status_service_contract_tier']) ? sanitize_text_field($input['status_service_contract_tier']) : '';
            $output['status_service_contract_upgrade_link'] = isset($input['status_service_contract_upgrade_link']) ? esc_url_raw($input['status_service_contract_upgrade_link']) : '';
            $output['support_ticket_link'] = isset($input['support_ticket_link']) ? esc_url_raw($input['support_ticket_link']) : '';
            $output['post_type_count_post_type'] = isset($input['post_type_count_post_type']) ? sanitize_key($input['post_type_count_post_type']) : '';

            $statuses = isset($input['post_type_count_statuses']) ? explode(',', $input['post_type_count_statuses']) : array();
            $statuses = array_filter(array_map('sanitize_key', array_map('trim', $statuses)));
            $output['post_type_count_statuses'] = implode(',', $statuses);

            $selected_forms = isset($input['conversion_selected_forms']) ? (array) $input['conversion_selected_forms'] : array();
            $output['conversion_selected_forms'] = array_values(array_filter(array_map('absint', $selected_forms)));

            return $output;
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

            if (!$screen) {
                return;
            }
            
            $screens = array(
                'toplevel_page_tempel-settings',
                'toplevel_page_tempel-widget-settings',
                'tempel-settings_page_tempel-widget-settings',
                'tempel-settings_page_tempel-login-settings',
            );
            
            if (in_array($screen->id, $screens, true)) {
                wp_enqueue_style('tmpl-settings-page', TEMPEL_SETTINGS_ASSET_URL . 'css/widget-settings.css');
                wp_enqueue_style('tmpl-settings-overrides', TEMPEL_SETTINGS_ASSET_URL . 'css/settings-overrides.css', array('tmpl-settings-page'));
                wp_enqueue_script('tmpl-settings-page', TEMPEL_SETTINGS_ASSET_URL . 'js/settings.js', array('jquery'), filemtime(TEMPEL_SETTINGS_ASSET_DIR . 'js/settings.js'), true);
                wp_localize_script('tmpl-settings-page', 'tmplWidgetSettings', array(
                    'nonce' => wp_create_nonce('tmpl_widget_settings_action'),
                ));
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
