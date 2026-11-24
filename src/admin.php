<?php

namespace Tempel;

// Views
require_once TEMPEL_SETTINGS_DIR . 'src/views/overview-settings.php';
require_once TEMPEL_SETTINGS_DIR . 'src/views/general-settings.php';
require_once TEMPEL_SETTINGS_DIR . 'src/views/widget-settings.php';
require_once TEMPEL_SETTINGS_DIR . 'src/views/mail-settings.php';
require_once TEMPEL_SETTINGS_DIR . 'src/views/gform-address-settings.php';
require_once TEMPEL_SETTINGS_DIR . 'src/views/performance-settings.php';
require_once TEMPEL_SETTINGS_DIR . 'src/views/info-settings.php';
require_once TEMPEL_SETTINGS_DIR . 'src/views/status-settings.php';
require_once TEMPEL_SETTINGS_DIR . 'src/views/backup-settings.php';
require_once TEMPEL_SETTINGS_DIR . 'src/views/stager-settings.php';
require_once TEMPEL_SETTINGS_DIR . 'src/views/ai-settings.php';
require_once TEMPEL_SETTINGS_DIR . 'src/views/dna-settings.php';
require_once TEMPEL_SETTINGS_DIR . 'src/views/optimization-settings.php';
require_once TEMPEL_SETTINGS_DIR . 'src/views/content-inventory-settings.php';
require_once TEMPEL_SETTINGS_DIR . 'src/views/templeton-settings.php';

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
        private static array $registered_pages = array();
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
            add_filter('option_page_capability_tempel_dna_settings', array($this, 'dna_settings_capability'));
            
            // Hook admin page assets
            add_action('admin_enqueue_scripts', array($this, 'load_assets'));
            add_filter('admin_body_class', array($this, 'add_admin_body_class'));
            
            // Hook the admin pages
            $this->load_pages();
            add_action('admin_menu', array($this, 'move_tempel_menu_to_bottom'), PHP_INT_MAX);
            add_action('admin_menu', array($this, 'simplify_submenu'), PHP_INT_MAX);
            add_action('admin_head', array($this, 'hide_detailed_submenu_items'));
            
            // Load widgets
            $this->load_widgets();
        }

        public function dna_settings_capability(): string
        {
            return 'edit_pages';
        }

        public function move_tempel_menu_to_bottom(): void
        {
            global $menu;

            if (!is_array($menu)) {
                return;
            }

            foreach ($menu as $position => $item) {
                if (($item[2] ?? '') !== 'tempel-settings') {
                    continue;
                }

                unset($menu[$position]);
                $menu[] = $item;
                break;
            }
        }

        /** Keep the WordPress menu calm; detailed screens remain available in the plugin navigation. */
        public function simplify_submenu(): void
        {
            global $submenu;
            if (empty($submenu['tempel-settings']) || !is_array($submenu['tempel-settings'])) {
                return;
            }

            foreach ($submenu['tempel-settings'] as &$item) {
                if (($item[2] ?? '') === 'tempel-backup-settings') {
                    $item[0] = __('Gereedschap', 'tempel-settings');
                } elseif (($item[2] ?? '') === 'tempel-status-settings') {
                    $item[0] = __('Logboek', 'tempel-settings');
                }
            }
            unset($item);
        }

        /**
         * Keep detail screens registered for WordPress capability checks while
         * hiding their duplicate links from the native submenu.
         */
        public function hide_detailed_submenu_items(): void
        {
            $hidden = array(
                'tempel-widget-settings',
                'tempel-gform-address-settings',
                'tempel-performance-settings',
                'tempel-mail-settings',
                'tempel-info-settings',
                'tempel-stager-settings',
                'tempel-ai-settings',
            );

            echo '<style id="tempel-settings-submenu-visibility">';
            foreach ($hidden as $slug) {
                echo '#toplevel_page_tempel-settings .wp-submenu a[href="admin.php?page=' . esc_attr($slug) . '"]{display:none!important}';
            }
            echo '</style>';
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
            if (self::$registered_pages) {
                $this->pages = self::$registered_pages;
                return;
            }
            $overview_page = new Overview_Settings(
                __('Overzicht', 'tempel-settings'),
                __('Tempel', 'tempel-settings'),
                'tempel-settings',
                $this->get_menu_icon(),
                99,
                '',
                false,
                'edit_pages',
            );

            $this->pages['tempel-settings'] = $overview_page;

            $this->pages['tempel-general-settings'] = new General_Settings(
                __('Beheer', 'tempel-settings'),
                __('Beheer', 'tempel-settings'),
                'tempel-general-settings',
                $this->get_menu_icon(),
                1,
                'tempel-settings',
                true
            );

            $this->pages['tempel-content-inventory'] = new Content_Inventory_Settings(
                __('Contentinventaris', 'tempel-settings'),
                __('Contentinventaris', 'tempel-settings'),
                'tempel-content-inventory',
                $this->get_menu_icon(),
                1,
                'tempel-settings',
                true
            );

            $this->pages['tempel-dna-settings'] = new DNA_Settings(
                __('DNA', 'tempel-settings'),
                __('DNA', 'tempel-settings'),
                'tempel-dna-settings',
                $this->get_menu_icon(),
                2,
                'tempel-settings',
                true,
                'edit_pages'
            );

            $this->pages['tempel-optimization-settings'] = new Optimization_Settings(
                __('Optimalisatie', 'tempel-settings'),
                __('Optimalisatie', 'tempel-settings'),
                'tempel-optimization-settings',
                $this->get_menu_icon(),
                3,
                'tempel-settings',
                true,
                'edit_pages'
            );
            
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
                __('Gravity Forms', 'tempel-settings'),
                __('Gravity Forms', 'tempel-settings'),
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

            $this->pages['tempel-ai-settings'] = new AI_Settings(
                __('AI-instellingen', 'tempel-settings'),
                __('AI-instellingen', 'tempel-settings'),
                'tempel-ai-settings',
                $this->get_menu_icon(),
                6,
                'tempel-settings',
                true
            );

            $this->pages['tempel-templeton-settings'] = new Templeton_Settings(
                __('Templeton Engine', 'tempel-settings'),
                __('Templeton Engine', 'tempel-settings'),
                'tempel-templeton-settings',
                $this->get_menu_icon(),
                7,
                'tempel-settings',
                true
            );

            $this->pages['tempel-info-settings'] = new Info_Settings(
                __('Info', 'tempel-settings'),
                __('Info', 'tempel-settings'),
                'tempel-info-settings',
                $this->get_menu_icon(),
                6,
                'tempel-settings',
                true
            );

            $this->pages['tempel-status-settings'] = new Status_Settings(
                __('Status & logboek', 'tempel-settings'),
                __('Status & logboek', 'tempel-settings'),
                'tempel-status-settings',
                $this->get_menu_icon(),
                7,
                'tempel-settings',
                true
            );

            $this->pages['tempel-backup-settings'] = new Backup_Settings(
                __('Back-ups', 'tempel-settings'),
                __('Back-ups', 'tempel-settings'),
                'tempel-backup-settings',
                $this->get_menu_icon(),
                8,
                'tempel-settings',
                true
            );

            $this->pages['tempel-stager-settings'] = new Stager_Settings(
                __('Website verhuizen', 'tempel-settings'),
                __('Website verhuizen', 'tempel-settings'),
                'tempel-stager-settings',
                $this->get_menu_icon(),
                9,
                'tempel-settings',
                true
            );

            self::$registered_pages = $this->pages;
        }
        
        public function get_pages()
        {
            return $this->pages;
        }

        public static function registered_pages(): array
        {
            return self::$registered_pages;
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

            register_setting(
                'tempel_backup_settings',
                Dropbox_Backup::SETTINGS_OPTION,
                array(
                    'sanitize_callback' => array($this, 'sanitize_backup_settings'),
                )
            );

            register_setting(
                'tempel_ai_settings',
                AI_Assistant::SETTINGS_OPTION,
                array(
                    'sanitize_callback' => array($this, 'sanitize_ai_settings'),
                )
            );

            register_setting(
                'tempel_dna_settings',
                AI_DNA::OPTION,
                array(
                    'sanitize_callback' => array(AI_DNA::class, 'sanitize'),
                )
            );

            register_setting(
                'tempel_dna_settings',
                AI_DNA::PROPOSAL_OPTION,
                array(
                    'sanitize_callback' => array(AI_DNA::class, 'sanitize'),
                )
            );

            register_setting(
                'tempel_content_inventory',
                Content_Inventory::OPTION,
                array(
                    'sanitize_callback' => array(Content_Inventory::class, 'sanitize'),
                )
            );

            register_setting(
                'tempel_templeton_guidelines',
                Templeton_Guidelines::OPTION,
                array(
                    'sanitize_callback' => array(Templeton_Guidelines::class, 'sanitize'),
                )
            );
        }

        public function sanitize_ai_settings($input): array
        {
            $input = is_array($input) ? $input : array();
            $current = get_option(AI_Assistant::SETTINGS_OPTION, array());
            $current = is_array($current) ? $current : array();
            $allowed_models = array('gpt-6.1-terra', 'gpt-6-luna', 'gpt-5.4-mini');
            $model = sanitize_text_field(wp_unslash($input['model'] ?? 'gpt-6.1-terra'));
            $roles = array_map('sanitize_key', (array) ($input['roles'] ?? array()));
            $roles = array_values(array_intersect($roles, array_keys(wp_roles()->roles)));

            $sanitized = array(
                'enabled' => isset($input['enabled']) && $input['enabled'] === 'on' ? 'on' : '',
                'api_key' => !empty($input['api_key'])
                    ? \Tempel\Core\SecretStore::encrypt(sanitize_text_field(wp_unslash($input['api_key'])))
                    : (string) ($current['api_key'] ?? ''),
                'model' => in_array($model, $allowed_models, true) ? $model : 'gpt-6.1-terra',
                'roles' => $roles ?: array('administrator', 'editor'),
                'daily_user_limit' => max(1, min(500, absint($input['daily_user_limit'] ?? 25))),
                'monthly_site_limit' => max(1, min(100000, absint($input['monthly_site_limit'] ?? 1000))),
                'monthly_token_limit' => max(10000, min(100000000, absint($input['monthly_token_limit'] ?? 1000000))),
                'max_input_chars' => max(500, min(50000, absint($input['max_input_chars'] ?? 12000))),
                'max_output_tokens' => max(100, min(4000, absint($input['max_output_tokens'] ?? 1200))),
            );

            return array_merge($sanitized, AI_DNA::sanitize($input));
        }

        public function sanitize_backup_settings($input): array
        {
            $input = is_array($input) ? $input : array();
            $current = get_option(Dropbox_Backup::SETTINGS_OPTION, array());
            $current = is_array($current) ? $current : array();
            $frequency = isset($input['frequency']) ? sanitize_key($input['frequency']) : 'weekly';
            $folder = isset($input['folder']) ? sanitize_text_field(wp_unslash($input['folder'])) : '/Tempel Backups';
            $folder = '/' . trim($folder, "/ \\t\\n\\r\\0\\x0B");

            return array(
                'enabled' => isset($input['enabled']) && $input['enabled'] === 'on' ? 'on' : '',
                'frequency' => in_array($frequency, array('weekly', 'monthly'), true) ? $frequency : 'weekly',
                'time' => isset($input['time']) && preg_match('/^(?:[01]\\d|2[0-3]):[0-5]\\d$/', (string) $input['time']) ? sanitize_text_field($input['time']) : '03:30',
                'retention' => max(1, min(24, absint($input['retention'] ?? 4))),
                'folder' => $folder,
                'app_key' => isset($input['app_key']) ? sanitize_text_field(wp_unslash($input['app_key'])) : '',
                'app_secret' => !empty($input['app_secret'])
                    ? \Tempel\Core\SecretStore::encrypt(sanitize_text_field(wp_unslash($input['app_secret'])))
                    : (string) ($current['app_secret'] ?? ''),
                'notification_email' => isset($input['notification_email']) ? sanitize_email(wp_unslash($input['notification_email'])) : 'info@studiotempel.nl',
                'notify_success' => isset($input['notify_success']) && $input['notify_success'] === 'on' ? 'on' : '',
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
                'admin_light_theme',
                'compact_publish_box',
                'complianz_close_button_fix',
                'notifications_enabled',
                'security_lock',
                'security_lock_auto_reenable',
                'sensitive_files_enabled',
                'email_login_verification',
                'form_entry_retention_enabled',
                'gf_antispam_enabled',
                'disable_comments',
                'disable_default_pt',
                'hide_dashboard_widgets',
                'skip_bundled_themes',
                'svg_support',
                'taxonomy_order',
                'duplicate_content',
                'user_switching',
                'gf_bag_address_enabled',
                'performance_enabled',
                'performance_disable_emojis',
                'performance_disable_embeds',
                'performance_disable_xmlrpc',
                'performance_disable_heartbeat',
            );

            $gform_address_submitted = $this->has_any_input_key($input, array(
                'gf_bag_address_enabled',
                'gf_bag_address_api_key',
                'gf_bag_address_endpoint',
                'gf_bag_address_timeout',
                'gf_bag_address_monthly_limit',
                'gf_bag_address_cache_days',
                'gf_bag_address_rate_limit',
                'form_entry_retention_enabled',
                'form_entry_retention_days',
                'gf_antispam_enabled',
                'gf_antispam_min_seconds',
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
                'admin_light_theme',
                'compact_publish_box',
                'complianz_close_button_fix',
                'notifications_enabled',
                'security_lock',
                'security_lock_auto_reenable',
                'sensitive_files_enabled',
                'email_login_verification',
                'disable_comments',
                'disable_default_pt',
                'hide_dashboard_widgets',
                'skip_bundled_themes',
                'svg_support',
                'taxonomy_order',
                'duplicate_content',
                'user_switching',
            ));

            foreach ($checkboxes as $key) {
                if (isset($input[$key])) {
                    $output[$key] = $input[$key] === 'on' ? 'on' : '';
                    continue;
                }

                $gform_checkbox = in_array($key, array('gf_bag_address_enabled', 'form_entry_retention_enabled', 'gf_antispam_enabled'), true);

                if ($gform_checkbox && $gform_address_submitted) {
                    $output[$key] = '';
                    continue;
                }

                if (strpos($key, 'performance_') === 0 && $performance_settings_submitted) {
                    $output[$key] = '';
                    continue;
                }

                if (!$gform_checkbox && strpos($key, 'performance_') !== 0 && $general_settings_submitted) {
                    $output[$key] = '';
                }
            }

            if (isset($input['gf_bag_address_api_key'])) {
                $api_key = sanitize_text_field($input['gf_bag_address_api_key']);

                if ($api_key !== '') {
                    $output['gf_bag_address_api_key'] = $api_key;
                }
            }

            if (isset($input['form_entry_retention_days'])) {
                $output['form_entry_retention_days'] = (string) max(1, min(3650, absint($input['form_entry_retention_days'])));
            }

            if (isset($input['gf_antispam_min_seconds'])) {
                $output['gf_antispam_min_seconds'] = (string) max(1, min(30, absint($input['gf_antispam_min_seconds'])));
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
            
            if ($this->is_tempel_settings_screen($screen->id)) {
                $current_page = isset($_GET['page']) ? sanitize_key(wp_unslash($_GET['page'])) : '';
                wp_enqueue_style('tempel-admin-tokens', TEMPEL_SETTINGS_ASSET_URL . 'css/admin-tokens.css', array(), filemtime(TEMPEL_SETTINGS_ASSET_DIR . 'css/admin-tokens.css'));
                wp_enqueue_style('tmpl-settings-page', TEMPEL_SETTINGS_ASSET_URL . 'css/widget-settings.css', array('tempel-admin-tokens'), filemtime(TEMPEL_SETTINGS_ASSET_DIR . 'css/widget-settings.css'));
                wp_enqueue_style('tmpl-settings-overrides', TEMPEL_SETTINGS_ASSET_URL . 'css/settings-overrides.css', array('tmpl-settings-page'), filemtime(TEMPEL_SETTINGS_ASSET_DIR . 'css/settings-overrides.css'));
                wp_enqueue_style('tempel-admin-components', TEMPEL_SETTINGS_ASSET_URL . 'css/admin-components.css', array('tmpl-settings-overrides'), filemtime(TEMPEL_SETTINGS_ASSET_DIR . 'css/admin-components.css'));

                if ($screen->id === 'tempel-settings_page_tempel-stager-settings') {
                    wp_enqueue_script('tempel-stager-folder-browser', TEMPEL_SETTINGS_ASSET_URL . 'js/stager-folder-browser.js', array(), filemtime(TEMPEL_SETTINGS_ASSET_DIR . 'js/stager-folder-browser.js'), true);
                    wp_enqueue_script('tempel-stager-deploy-progress', TEMPEL_SETTINGS_ASSET_URL . 'js/stager-deploy-progress.js', array(), filemtime(TEMPEL_SETTINGS_ASSET_DIR . 'js/stager-deploy-progress.js'), true);
                    wp_enqueue_script('tempel-stager-export-progress', TEMPEL_SETTINGS_ASSET_URL . 'js/stager-export-progress.js', array(), filemtime(TEMPEL_SETTINGS_ASSET_DIR . 'js/stager-export-progress.js'), true);
                }

                if ($screen->id === 'tempel-settings_page_tempel-backup-settings') {
                    wp_enqueue_script('tempel-backup-status', TEMPEL_SETTINGS_ASSET_URL . 'js/backup-status.js', array(), filemtime(TEMPEL_SETTINGS_ASSET_DIR . 'js/backup-status.js'), true);
                    wp_localize_script('tempel-backup-status', 'tempelBackupStatus', array(
                        'ajaxUrl' => admin_url('admin-ajax.php'),
                        'nonce' => wp_create_nonce('tempel_dropbox_backup_status'),
                        'interval' => 2500,
                        'labels' => array(
                            'idle' => __('Nog geen back-up', 'tempel-settings'),
                            'queued' => __('In wachtrij', 'tempel-settings'),
                            'running' => __('Wordt gemaakt', 'tempel-settings'),
                            'uploading' => __('Wordt verstuurd', 'tempel-settings'),
                            'success' => __('Geslaagd', 'tempel-settings'),
                            'error' => __('Mislukt', 'tempel-settings'),
                            'connected' => __('Klaar', 'tempel-settings'),
                            'disconnected' => __('Ontkoppeld', 'tempel-settings'),
                            'unavailable' => __('Status tijdelijk niet beschikbaar.', 'tempel-settings'),
                        ),
                    ));
                }

                if ($current_page === 'tempel-dna-settings') {
                    wp_enqueue_script('tempel-ai-dna', TEMPEL_SETTINGS_ASSET_URL . 'js/ai-dna.js', array('wp-api-fetch'), filemtime(TEMPEL_SETTINGS_ASSET_DIR . 'js/ai-dna.js'), true);
                    wp_localize_script('tempel-ai-dna', 'tempelDNA', array(
                        'path' => '/tempel/v1/ai/dna',
                        'startPath' => '/tempel/v1/ai/dna/start',
                        'statusPath' => '/tempel/v1/ai/dna/status/',
                        'nonce' => wp_create_nonce('wp_rest'),
                        'fields' => array_map(static function (array $field): array {
                            return array('label' => (string) $field['label'], 'group' => (string) ($field['group'] ?? ''));
                        }, AI_DNA::fields()),
                        'groups' => AI_DNA::groups(),
                    ));
                }
                if ($current_page === 'tempel-optimization-settings') {
                    wp_enqueue_script('tempel-ai-optimization', TEMPEL_SETTINGS_ASSET_URL . 'js/ai-optimization.js', array('wp-api-fetch'), filemtime(TEMPEL_SETTINGS_ASSET_DIR . 'js/ai-optimization.js'), true);
                    wp_localize_script('tempel-ai-optimization', 'tempelOptimization', array(
                        'startPath' => '/tempel/v1/ai/optimization/start',
                        'statusPath' => '/tempel/v1/ai/optimization/status/',
                        'nonce' => wp_create_nonce('wp_rest'),
                    ));
                }
                // Use the release version so browsers cannot keep an older settings script
                // after a plugin update whose source file timestamp stayed unchanged.
                wp_enqueue_script('tmpl-settings-page', TEMPEL_SETTINGS_ASSET_URL . 'js/settings.js', array('jquery'), TEMPEL_SETTINGS_VERSION, true);
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

        public function add_admin_body_class(string $classes): string
        {
            $screen = get_current_screen();

            if ($screen && $this->is_tempel_settings_screen($screen->id)) {
                $classes .= ' tempel-settings-admin';
                $settings = get_option('tmpl_settings', array());
                if (is_array($settings) && ($settings['admin_light_theme'] ?? '') === 'on') {
                    $classes .= ' tempel-theme-light';
                }
            }

            return trim($classes);
        }

        private function is_tempel_settings_screen(string $screen_id): bool
        {
            if ($screen_id === 'toplevel_page_tempel-settings'
                || $screen_id === 'toplevel_page_tempel-widget-settings'
                || str_starts_with($screen_id, 'tempel-settings_page_tempel-')
                || str_starts_with($screen_id, 'admin_page_tempel-')) {
                return true;
            }

            $page = isset($_GET['page']) ? sanitize_key(wp_unslash($_GET['page'])) : '';
            return $page !== '' && isset(self::$registered_pages[$page]);
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
