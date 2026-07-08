<?php

namespace Tempel;

// Views
require_once TEMPEL_SETTINGS_DIR . 'src/views/general-settings.php';
require_once TEMPEL_SETTINGS_DIR . 'src/views/widget-settings.php';
require_once TEMPEL_SETTINGS_DIR . 'src/views/status-settings.php';
require_once TEMPEL_SETTINGS_DIR . 'src/views/mail-settings.php';
require_once TEMPEL_SETTINGS_DIR . 'src/views/gform-address-settings.php';
require_once TEMPEL_SETTINGS_DIR . 'src/views/performance-settings.php';

// Widgets
require_once TEMPEL_SETTINGS_DIR . 'src/widgets/status-widget.php';
require_once TEMPEL_SETTINGS_DIR . 'src/widgets/support-widget.php';
require_once TEMPEL_SETTINGS_DIR . 'src/widgets/conversion-widget.php';
require_once TEMPEL_SETTINGS_DIR . 'src/widgets/blog-widget.php';
require_once TEMPEL_SETTINGS_DIR . 'src/widgets/analytics-widget.php';


require_once TEMPEL_SETTINGS_DIR . 'src/includes/helper-functions.php';

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
            require_once TEMPEL_SETTINGS_DIR . 'src/includes/ajax-functions.php';
            add_action('wp_ajax_tempel_test_postcode_api', array($this, 'test_postcode_api'));
            add_action('admin_post_tempel_send_user_mail', array($this, 'send_user_mail'));
        }

        public function test_postcode_api(): void
        {
            check_ajax_referer('tempel_postcode_api_test', 'nonce');

            if (!current_user_can('manage_options')) {
                wp_send_json_error(
                    array('message' => __('Je hebt geen rechten om deze test uit te voeren.', 'tempel-settings')),
                    403
                );
            }

            if (!class_exists('Tempel\GF_BAG_Address')) {
                wp_send_json_error(
                    array('message' => __('De Gform adres veld module is niet beschikbaar.', 'tempel-settings')),
                    500
                );
            }

            $postcode = isset($_POST['postcode']) ? sanitize_text_field(wp_unslash($_POST['postcode'])) : '';
            $huisnummer = isset($_POST['huisnummer']) ? sanitize_text_field(wp_unslash($_POST['huisnummer'])) : '';
            $api_key = isset($_POST['api_key']) ? sanitize_text_field(wp_unslash($_POST['api_key'])) : '';
            $endpoint = isset($_POST['endpoint']) ? esc_url_raw(wp_unslash($_POST['endpoint'])) : '';
            $result = GF_BAG_Address::test_connection($postcode, $huisnummer, $api_key, $endpoint);

            if (empty($result['success'])) {
                wp_send_json_error(
                    array(
                        'message' => $result['message'] ?? __('Verbinding mislukt.', 'tempel-settings'),
                    ),
                    (int) ($result['status'] ?? 422)
                );
            }

            $data = $result['data'] ?? array();
            $address = trim(sprintf(
                '%1$s %2$s, %3$s',
                (string) ($data['straat'] ?? ''),
                (string) ($data['huisnummer'] ?? ''),
                (string) ($data['plaats'] ?? '')
            ));

            wp_send_json_success(
                array(
                    'message' => $address
                        ? sprintf(__('Verbinding werkt. Adres: %s', 'tempel-settings'), $address)
                        : __('Verbinding werkt.', 'tempel-settings'),
                )
            );
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
            $general_page = new General_Settings(
                __('General', 'tempel-settings'),
                __('Tempel Settings', 'tempel-settings'),
                'tempel-settings',
                $this->get_menu_icon(),
                99,
            );

            $status_page = new Status_Settings(
                __('Status', 'tempel-settings'),
                __('Status', 'tempel-settings'),
                'tempel-status-settings',
                $this->get_menu_icon(),
                1,
                'tempel-settings',
                true
            );

            $this->pages['tempel-status-settings'] = $status_page;
            $this->pages['tempel-settings'] = $general_page;
            
            $this->pages['tempel-widget-settings'] = new Widget_Settings(
                __('Widgets', 'tempel-settings'),
                __('Widgets', 'tempel-settings'),
                'tempel-widget-settings',
                $this->get_menu_icon(),
                2,
                'tempel-settings',
                true
            );

            $this->pages['tempel-gform-address-settings'] = new Gform_Address_Settings(
                __('Gform adres veld', 'tempel-settings'),
                __('Gform adres veld', 'tempel-settings'),
                'tempel-gform-address-settings',
                $this->get_menu_icon(),
                3,
                'tempel-settings',
                true
            );

            $this->pages['tempel-performance-settings'] = new Performance_Settings(
                __('Performance', 'tempel-settings'),
                __('Performance', 'tempel-settings'),
                'tempel-performance-settings',
                $this->get_menu_icon(),
                4,
                'tempel-settings',
                true
            );

            $this->pages['tempel-mail-settings'] = new Mail_Settings(
                __('Mail', 'tempel-settings'),
                __('Mail', 'tempel-settings'),
                'tempel-mail-settings',
                $this->get_menu_icon(),
                5,
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
                'performance_enabled',
                'performance_disable_emojis',
                'performance_disable_embeds',
                'performance_disable_xmlrpc',
                'performance_disable_heartbeat',
            );

            $gform_address_submitted = $this->has_any_input_key($input, array(
                'gf_bag_address_api_key',
                'gf_bag_address_endpoint',
                'gf_bag_address_timeout',
                'gf_bag_address_monthly_limit',
                'gf_bag_address_cache_days',
                'gf_bag_address_rate_limit',
            ));
            $performance_settings_submitted = $this->has_any_input_key($input, array(
                'performance_enabled',
                'performance_frontend_memory_limit',
                'performance_admin_memory_limit',
                'performance_revision_limit',
                'performance_heartbeat_interval',
                'performance_disable_heartbeat',
                'performance_disable_emojis',
                'performance_disable_embeds',
                'performance_disable_xmlrpc',
            ));
            $general_settings_submitted = $this->has_any_input_key($input, array(
                'enable_branding',
                'disable_comments',
                'disable_default_pt',
                'hide_dashboard_widgets',
                'svg_support',
                'taxonomy_order',
            ));

            foreach ($checkboxes as $key) {
                if (isset($input[$key])) {
                    $output[$key] = $input[$key] === 'on' ? 'on' : '';
                    continue;
                }

                if ($key === 'gf_bag_address_enabled' && $gform_address_submitted) {
                    $output[$key] = '';
                    continue;
                }

                if (strpos($key, 'performance_') === 0 && $performance_settings_submitted) {
                    $output[$key] = '';
                    continue;
                }

                if ($key !== 'gf_bag_address_enabled' && strpos($key, 'performance_') !== 0 && $general_settings_submitted) {
                    $output[$key] = '';
                }
            }

            if (isset($input['gf_bag_address_api_key'])) {
                $api_key = sanitize_text_field($input['gf_bag_address_api_key']);

                if ($api_key !== '') {
                    $output['gf_bag_address_api_key'] = $api_key;
                }
            }

            if (isset($input['gf_bag_address_endpoint'])) {
                $output['gf_bag_address_endpoint'] = esc_url_raw($input['gf_bag_address_endpoint']);
            }

            if (isset($input['gf_bag_address_timeout'])) {
                $output['gf_bag_address_timeout'] = (string) max(1, min(30, absint($input['gf_bag_address_timeout'])));
            }

            if (isset($input['gf_bag_address_monthly_limit'])) {
                $output['gf_bag_address_monthly_limit'] = (string) max(0, min(1000000, absint($input['gf_bag_address_monthly_limit'])));
            }

            if (isset($input['gf_bag_address_cache_days'])) {
                $output['gf_bag_address_cache_days'] = (string) max(0, min(365, absint($input['gf_bag_address_cache_days'])));
            }

            if (isset($input['gf_bag_address_rate_limit'])) {
                $output['gf_bag_address_rate_limit'] = (string) max(1, min(300, absint($input['gf_bag_address_rate_limit'])));
            }

            if (isset($input['performance_frontend_memory_limit'])) {
                $output['performance_frontend_memory_limit'] = (string) max(64, min(1024, absint($input['performance_frontend_memory_limit'])));
            }

            if (isset($input['performance_admin_memory_limit'])) {
                $output['performance_admin_memory_limit'] = (string) max(64, min(1024, absint($input['performance_admin_memory_limit'])));
            }

            if (isset($input['performance_revision_limit'])) {
                $output['performance_revision_limit'] = (string) max(1, min(50, absint($input['performance_revision_limit'])));
            }

            if (isset($input['performance_heartbeat_interval'])) {
                $output['performance_heartbeat_interval'] = (string) max(15, min(120, absint($input['performance_heartbeat_interval'])));
            }

            return $output;
        }

        private function has_any_input_key(array $input, array $keys): bool
        {
            foreach ($keys as $key) {
                if (array_key_exists($key, $input)) {
                    return true;
                }
            }

            return false;
        }

        public function send_user_mail(): void
        {
            if (!current_user_can('manage_options')) {
                wp_die(esc_html__('Je hebt geen rechten om mails te versturen.', 'tempel-settings'));
            }

            check_admin_referer('tempel_send_user_mail', 'tempel_send_user_mail_nonce');

            $recipient_ids = isset($_POST['tempel_mail_recipients']) ? (array) wp_unslash($_POST['tempel_mail_recipients']) : array();
            $recipient_ids = array_values(array_filter(array_map('absint', $recipient_ids)));
            $subject = isset($_POST['tempel_mail_subject']) ? sanitize_text_field(wp_unslash($_POST['tempel_mail_subject'])) : '';
            $message = isset($_POST['tempel_mail_message']) ? wp_kses_post(wp_unslash($_POST['tempel_mail_message'])) : '';

            if ($subject === '') {
                $subject = Mail_Settings::get_default_subject();
            }

            if (trim(wp_strip_all_tags($message)) === '') {
                $message = Mail_Settings::get_default_message();
            }

            $this->save_mail_draft($recipient_ids, $subject, $message);

            if (empty($recipient_ids) || $subject === '' || trim(wp_strip_all_tags($message)) === '') {
                $this->redirect_mail_page(array('tempel_mail_error' => 'missing'));
            }

            $headers = array('Content-Type: text/html; charset=UTF-8');
            $sent = 0;
            $failed = 0;

            foreach ($recipient_ids as $recipient_id) {
                $user = get_user_by('id', $recipient_id);

                if (!$user || !is_email($user->user_email)) {
                    $failed++;
                    continue;
                }

                $personal_subject = $this->replace_mail_tags($subject, $user);
                $personal_message = $this->replace_mail_tags($message, $user);
                $mail_sent = wp_mail($user->user_email, $personal_subject, wpautop($personal_message), $headers);

                if ($mail_sent) {
                    $sent++;
                    continue;
                }

                $failed++;
            }

            $this->redirect_mail_page(array(
                'tempel_mail_sent' => $sent,
                'tempel_mail_failed' => $failed,
            ));
        }

        private function redirect_mail_page(array $args): void
        {
            wp_safe_redirect(add_query_arg($args, admin_url('admin.php?page=tempel-mail-settings')));
            exit;
        }

        private function save_mail_draft(array $recipient_ids, string $subject, string $message): void
        {
            update_option(
                'tmpl_mail_settings',
                array(
                    'recipients' => array_values(array_filter(array_map('absint', $recipient_ids))),
                    'subject' => sanitize_text_field($subject),
                    'message' => wp_kses_post($message),
                ),
                false
            );
        }

        private function replace_mail_tags(string $content, \WP_User $user): string
        {
            $first_name = (string) get_user_meta($user->ID, 'first_name', true);
            $last_name = (string) get_user_meta($user->ID, 'last_name', true);
            $name = trim($first_name . ' ' . $last_name);

            if ($name === '') {
                $name = $user->display_name;
            }

            $replacements = array(
                '[naam]' => $name,
                '[voornaam]' => $first_name !== '' ? $first_name : $user->display_name,
                '[achternaam]' => $last_name,
                '[email]' => $user->user_email,
                '[website_url]' => home_url('/'),
                '[website url]' => home_url('/'),
                '[website_naam]' => get_bloginfo('name'),
            );

            return str_replace(array_keys($replacements), array_values($replacements), $content);
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
                'tempel-settings_page_tempel-status-settings',
                'tempel-settings_page_tempel-widget-settings',
                'tempel-settings_page_tempel-mail-settings',
                'tempel-settings_page_tempel-gform-address-settings',
                'tempel-settings_page_tempel-performance-settings',
            );
            
            if (in_array($screen->id, $screens, true)) {
                wp_enqueue_style('tmpl-settings-page', TEMPEL_SETTINGS_ASSET_URL . 'css/widget-settings.css');
                wp_enqueue_style('tmpl-settings-overrides', TEMPEL_SETTINGS_ASSET_URL . 'css/settings-overrides.css', array('tmpl-settings-page'));
                wp_enqueue_script('tmpl-settings-page', TEMPEL_SETTINGS_ASSET_URL . 'js/settings.js', array('jquery'), filemtime(TEMPEL_SETTINGS_ASSET_DIR . 'js/settings.js'), true);
                wp_localize_script('tmpl-settings-page', 'tmplWidgetSettings', array(
                    'nonce' => wp_create_nonce('tmpl_widget_settings_action'),
                ));

                if ($screen->id === 'tempel-settings_page_tempel-gform-address-settings') {
                    wp_localize_script('tmpl-settings-page', 'tempelPostcodeApiTest', array(
                        'ajaxUrl' => admin_url('admin-ajax.php'),
                        'nonce' => wp_create_nonce('tempel_postcode_api_test'),
                        'messages' => array(
                            'testing' => __('API verbinding testen...', 'tempel-settings'),
                            'error' => __('Test mislukt.', 'tempel-settings'),
                        ),
                    ));
                }
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
