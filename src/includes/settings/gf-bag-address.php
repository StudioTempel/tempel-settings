<?php

namespace Tempel;

class GF_BAG_Address
{
    private const DEFAULT_ENDPOINT = 'https://api.postcodeapi.nu/v3/lookup';
    private const LEGACY_BAG_ENDPOINT = 'https://api.bag.kadaster.nl/lvbag/individuelebevragingen/v2/adressenuitgebreid';
    private const USAGE_OPTION = 'tmpl_gf_bag_address_usage';
    private const LAST_ERROR_OPTION = 'tmpl_gf_bag_address_last_error';
    private const LAST_TEST_OPTION = 'tmpl_gf_bag_address_last_test';

    private static bool $field_registered = false;

    private string $ajax_action = 'tmpl_gf_bag_address_lookup';

    public function __construct()
    {
        add_action('gform_loaded', array($this, 'register_field'), 5);
        add_action('wp_ajax_' . $this->ajax_action, array($this, 'lookup'));
        add_action('wp_ajax_nopriv_' . $this->ajax_action, array($this, 'lookup'));
        add_action('rest_api_init', array($this, 'register_rest_route'));
        add_action('admin_notices', array($this, 'render_usage_notice'));

        $this->register_field();
    }

    public function register_field(): void
    {
        if (self::$field_registered) {
            return;
        }

        if (!class_exists('GF_Fields') || !class_exists('GF_Field')) {
            return;
        }

        require_once TEMPEL_SETTINGS_DIR . 'src/includes/settings/fields/gf-field-nl-address.php';

        \GF_Fields::register(new \GF_Field_NL_Address());

        self::$field_registered = true;
    }

    public function register_rest_route(): void
    {
        register_rest_route(
            'tempel-settings/v1',
            '/postcode-lookup',
            array(
                'methods' => \WP_REST_Server::CREATABLE,
                'callback' => array($this, 'lookup_rest'),
                'permission_callback' => '__return_true',
                'args' => array(
                    'postcode' => array('sanitize_callback' => 'sanitize_text_field'),
                    'huisnummer' => array('sanitize_callback' => 'sanitize_text_field'),
                    'toevoeging' => array('sanitize_callback' => 'sanitize_text_field'),
                ),
            )
        );
    }

    public function lookup(): void
    {
        check_ajax_referer($this->ajax_action, 'nonce');

        $postcode = isset($_POST['postcode']) ? sanitize_text_field(wp_unslash($_POST['postcode'])) : '';
        $huisnummer = isset($_POST['huisnummer']) ? sanitize_text_field(wp_unslash($_POST['huisnummer'])) : '';
        $toevoeging = isset($_POST['toevoeging']) ? sanitize_text_field(wp_unslash($_POST['toevoeging'])) : '';
        $result = $this->lookup_address($postcode, $huisnummer, $toevoeging);

        if (!$result['success']) {
            wp_send_json_error(
                array(
                    'message' => $result['message'] ?? __('Adres niet gevonden.', 'tempel-settings'),
                ),
                (int) ($result['status'] ?? 422)
            );
        }

        wp_send_json_success($result['data']);
    }

    public function lookup_rest(\WP_REST_Request $request)
    {
        $result = $this->lookup_address(
            (string) $request->get_param('postcode'),
            (string) $request->get_param('huisnummer'),
            (string) $request->get_param('toevoeging')
        );

        if (!$result['success']) {
            return new \WP_Error(
                'tempel_postcode_lookup_failed',
                $result['message'] ?? __('Adres niet gevonden.', 'tempel-settings'),
                array('status' => (int) ($result['status'] ?? 422))
            );
        }

        return rest_ensure_response($result['data']);
    }

    private function lookup_address(string $postcode, string $huisnummer, string $toevoeging = '', string $api_key = '', string $endpoint = ''): array
    {
        if ($postcode === '' || $huisnummer === '') {
            return array(
                'success' => false,
                'message' => __('Postcode en huisnummer zijn verplicht.', 'tempel-settings'),
                'status' => 400,
            );
        }

        return $this->bag_lookup($postcode, $huisnummer, $toevoeging, $api_key, $endpoint);
    }

    public static function get_settings(): array
    {
        return array(
            'api_key' => (string) return_option('tmpl_settings', 'gf_bag_address_api_key'),
            'endpoint' => self::get_endpoint(),
            'timeout' => max(1, (int) (return_option('tmpl_settings', 'gf_bag_address_timeout') ?: 8)),
            'monthly_limit' => self::get_int_setting('gf_bag_address_monthly_limit', 1000, 0, 1000000),
            'cache_days' => self::get_int_setting('gf_bag_address_cache_days', 30, 0, 365),
            'rate_limit' => self::get_int_setting('gf_bag_address_rate_limit', 20, 1, 300),
        );
    }

    public static function get_health_status(): array
    {
        $settings = self::get_settings();
        $last_test = self::get_last_test();
        $last_error = self::get_last_error();
        $has_api_key = trim($settings['api_key']) !== '';

        $connection_status = 'neutral';
        $connection_message = __('Nog niet getest.', 'tempel-settings');

        if (!empty($last_test)) {
            $connection_status = !empty($last_test['success']) ? 'ok' : 'error';
            $connection_message = (string) ($last_test['message'] ?? $connection_message);
        } elseif (!empty($last_error['message'])) {
            $connection_status = 'error';
            $connection_message = (string) $last_error['message'];
        } elseif (!$has_api_key) {
            $connection_status = 'warning';
            $connection_message = __('API-sleutel ontbreekt.', 'tempel-settings');
        }

        return array(
            'gravity_forms' => array(
                'label' => __('Gravity Forms', 'tempel-settings'),
                'status' => class_exists('GFForms') || class_exists('GF_Fields') ? 'ok' : 'error',
                'message' => class_exists('GFForms') || class_exists('GF_Fields') ? __('Actief', 'tempel-settings') : __('Niet actief', 'tempel-settings'),
            ),
            'api_key' => array(
                'label' => __('API-key', 'tempel-settings'),
                'status' => $has_api_key ? 'ok' : 'warning',
                'message' => $has_api_key ? __('Aanwezig', 'tempel-settings') : __('Ontbreekt', 'tempel-settings'),
            ),
            'api_connection' => array(
                'label' => __('PostcodeAPI', 'tempel-settings'),
                'status' => $connection_status,
                'message' => $connection_message,
            ),
            'cache' => array(
                'label' => __('Cache', 'tempel-settings'),
                'status' => $settings['cache_days'] > 0 ? 'ok' : 'warning',
                'message' => $settings['cache_days'] > 0
                    ? sprintf(__('Actief, %d dagen', 'tempel-settings'), $settings['cache_days'])
                    : __('Uitgeschakeld', 'tempel-settings'),
            ),
            'performance' => array(
                'label' => __('Performance', 'tempel-settings'),
                'status' => sanitize_checkbox_value(return_option('tmpl_settings', 'performance_enabled')) ? 'ok' : 'warning',
                'message' => sanitize_checkbox_value(return_option('tmpl_settings', 'performance_enabled')) ? __('Actief', 'tempel-settings') : __('Niet actief', 'tempel-settings'),
            ),
        );
    }

    public static function test_connection(string $postcode, string $huisnummer, string $api_key = '', string $endpoint = ''): array
    {
        $lookup = new self();
        $result = $lookup->lookup_address($postcode, $huisnummer, '', $api_key, $endpoint);
        self::record_last_test($result);

        return $result;
    }

    private static function get_endpoint(): string
    {
        $endpoint = (string) return_option('tmpl_settings', 'gf_bag_address_endpoint');

        if ($endpoint === '' || rtrim($endpoint, '/') === self::LEGACY_BAG_ENDPOINT) {
            return self::DEFAULT_ENDPOINT;
        }

        return $endpoint;
    }

    public static function get_ajax_config(int $field_id, bool $manual_input = false, array $messages = array()): array
    {
        return array(
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'restUrl' => rest_url('tempel-settings/v1/postcode-lookup'),
            'action' => 'tmpl_gf_bag_address_lookup',
            'nonce' => wp_create_nonce('tmpl_gf_bag_address_lookup'),
            'fieldId' => $field_id,
            'manualInput' => $manual_input,
            'messages' => array(
                'loading' => $messages['loading'] ?? __('We zoeken je adres...', 'tempel-settings'),
                'notFound' => $messages['notFound'] ?? __('Geen adres gevonden.', 'tempel-settings'),
                'postcodeTooShort' => __('Postcode is nog niet compleet.', 'tempel-settings'),
                'invalidPostcode' => __('Controleer je postcode.', 'tempel-settings'),
            ),
        );
    }

    private function bag_lookup(string $postcode, string $huisnummer, string $toevoeging = '', string $api_key_override = '', string $endpoint_override = ''): array
    {
        $settings = self::get_settings();
        $api_key = trim($api_key_override) !== '' ? trim($api_key_override) : trim($settings['api_key']);
        $endpoint = trim($endpoint_override) !== '' ? $endpoint_override : $settings['endpoint'];
        $endpoint = $this->normalize_endpoint($endpoint);

        if ($api_key === '') {
            self::record_last_error(__('PostcodeAPI.nu API key ontbreekt.', 'tempel-settings'));

            return array(
                'success' => false,
                'message' => __('PostcodeAPI.nu API key ontbreekt in de Tempel Settings plugininstellingen.', 'tempel-settings'),
            );
        }

        $postcode = $this->normalize_postcode($postcode);
        $huisnummer = $this->normalize_house_number($huisnummer);

        if (strlen($postcode) > 0 && strlen($postcode) < 6) {
            return array(
                'success' => false,
                'message' => __('Postcode is nog niet compleet.', 'tempel-settings'),
                'status' => 400,
            );
        }

        if (!preg_match('/^[1-9][0-9]{3}[A-Z]{2}$/', $postcode)) {
            return array(
                'success' => false,
                'message' => __('Controleer je postcode.', 'tempel-settings'),
                'status' => 400,
            );
        }

        if ($huisnummer <= 0) {
            return array(
                'success' => false,
                'message' => __('Vul een geldig huisnummer in, alleen het nummer zonder toevoeging.', 'tempel-settings'),
                'status' => 400,
            );
        }

        $cache_key = $this->get_cache_key($postcode, $huisnummer);
        $cached = $this->get_cached_address($cache_key);

        if ($cached) {
            $cached['toevoeging'] = strtoupper(trim($toevoeging));

            return array(
                'success' => true,
                'data' => $cached,
            );
        }

        if (!$this->check_rate_limit($settings['rate_limit'])) {
            return array(
                'success' => false,
                'message' => __('Er zijn tijdelijk te veel adresaanvragen gedaan. Probeer het zo opnieuw.', 'tempel-settings'),
                'status' => 429,
            );
        }

        if ($settings['monthly_limit'] > 0 && self::get_usage_count() >= $settings['monthly_limit']) {
            return array(
                'success' => false,
                'message' => __('De maandlimiet voor PostcodeAPI.nu aanvragen is bereikt.', 'tempel-settings'),
                'status' => 429,
            );
        }

        $response = wp_remote_get(
            trailingslashit($endpoint) . rawurlencode($postcode) . '/' . rawurlencode((string) $huisnummer),
            array(
                'timeout' => $settings['timeout'],
                'headers' => array(
                    'Accept' => 'application/json',
                    'X-Api-Key' => $api_key,
                ),
            )
        );

        if (is_wp_error($response)) {
            self::record_last_error($response->get_error_message());

            return array(
                'success' => false,
                'message' => $response->get_error_message(),
            );
        }

        self::increment_usage();

        $status_code = (int) wp_remote_retrieve_response_code($response);
        $body = json_decode((string) wp_remote_retrieve_body($response), true);

        if ($status_code >= 400) {
            $message = $body['message'] ?? $body['detail'] ?? $body['title'] ?? __('PostcodeAPI.nu gaf een fout terug.', 'tempel-settings');

            if ($status_code === 404 || $message === 'Resource not found') {
                $message = __('Geen adres gevonden.', 'tempel-settings');
            }

            if ($status_code === 400 && $message === 'Request validation failed') {
                $message = __('PostcodeAPI.nu kon de aanvraag niet valideren. Controleer of het endpoint eindigt op /v3/lookup en gebruik alleen postcode plus huisnummer.', 'tempel-settings');
            }

            if ($status_code !== 404) {
                self::record_last_error($message, $status_code);
            }

            return array(
                'success' => false,
                'message' => $message,
                'status' => $status_code,
            );
        }

        if (!is_array($body) || empty($body['street']) || empty($body['city'])) {
            return array(
                'success' => false,
                'message' => __('Geen adres gevonden voor deze combinatie.', 'tempel-settings'),
                'status' => 404,
            );
        }

        $data = array(
            'postcode' => $body['postcode'] ?? $postcode,
            'huisnummer' => isset($body['number']) ? (string) $body['number'] : (string) $huisnummer,
            'toevoeging' => strtoupper(trim($toevoeging)),
            'straat' => $body['street'] ?? '',
            'plaats' => $body['city'] ?? '',
        );

        $this->set_cached_address($cache_key, $data, $settings['cache_days']);
        self::clear_last_error();

        return array(
            'success' => true,
            'data' => $data,
        );
    }

    public static function get_usage(): array
    {
        $month = current_time('Y-m');
        $usage = get_option(self::USAGE_OPTION, array());

        if (!is_array($usage) || ($usage['month'] ?? '') !== $month) {
            $usage = array(
                'month' => $month,
                'count' => 0,
            );
            update_option(self::USAGE_OPTION, $usage, false);
        }

        return $usage;
    }

    public static function get_usage_count(): int
    {
        $usage = self::get_usage();

        return max(0, (int) ($usage['count'] ?? 0));
    }

    public static function get_monthly_limit(): int
    {
        return self::get_int_setting('gf_bag_address_monthly_limit', 1000, 0, 1000000);
    }

    public static function get_usage_percentage(): int
    {
        $limit = self::get_monthly_limit();

        if ($limit <= 0) {
            return 0;
        }

        return (int) floor((self::get_usage_count() / $limit) * 100);
    }

    public static function is_monthly_limit_reached(int $limit = 0): bool
    {
        $limit = $limit > 0 ? $limit : self::get_monthly_limit();

        return $limit > 0 && self::get_usage_count() >= $limit;
    }

    public static function increment_usage(): void
    {
        $usage = self::get_usage();
        $usage['count'] = max(0, (int) ($usage['count'] ?? 0)) + 1;

        update_option(self::USAGE_OPTION, $usage, false);
    }

    private static function get_int_setting(string $key, int $default, int $min, int $max): int
    {
        $value = return_option('tmpl_settings', $key);

        if ($value === '' || $value === null) {
            return $default;
        }

        return max($min, min($max, absint($value)));
    }

    public static function record_last_error(string $message, int $status = 0): void
    {
        update_option(
            self::LAST_ERROR_OPTION,
            array(
                'message' => $message,
                'status' => $status,
                'time' => current_time('timestamp'),
            ),
            false
        );
    }

    public static function clear_last_error(): void
    {
        delete_option(self::LAST_ERROR_OPTION);
    }

    public static function get_last_error(): array
    {
        $error = get_option(self::LAST_ERROR_OPTION, array());

        return is_array($error) ? $error : array();
    }

    public static function record_last_test(array $result): void
    {
        $message = !empty($result['success'])
            ? __('Verbinding werkt.', 'tempel-settings')
            : (string) ($result['message'] ?? __('Verbinding mislukt.', 'tempel-settings'));

        update_option(
            self::LAST_TEST_OPTION,
            array(
                'success' => !empty($result['success']),
                'message' => $message,
                'time' => current_time('timestamp'),
            ),
            false
        );
    }

    public static function get_last_test(): array
    {
        $test = get_option(self::LAST_TEST_OPTION, array());

        return is_array($test) ? $test : array();
    }

    public function render_usage_notice(): void
    {
        if (!current_user_can('manage_options') || !sanitize_checkbox_value(return_option('tmpl_settings', 'gf_bag_address_enabled'))) {
            return;
        }

        $last_error = self::get_last_error();

        if (!empty($last_error['message'])) {
            $time = !empty($last_error['time']) ? date_i18n(get_option('date_format') . ' ' . get_option('time_format'), (int) $last_error['time']) : '';
            $message = $time
                ? sprintf(__('PostcodeAPI.nu verbinding/API-fout sinds %1$s: %2$s', 'tempel-settings'), $time, $last_error['message'])
                : sprintf(__('PostcodeAPI.nu verbinding/API-fout: %s', 'tempel-settings'), $last_error['message']);

            printf(
                '<div class="notice notice-error"><p>%s</p></div>',
                esc_html($message)
            );
        }

        $limit = self::get_monthly_limit();

        if ($limit <= 0) {
            return;
        }

        $count = self::get_usage_count();
        $percentage = self::get_usage_percentage();

        if ($percentage < 75) {
            return;
        }

        $class = $percentage >= 100 ? 'notice notice-error' : 'notice notice-warning';

        printf(
            '<div class="%1$s"><p>%2$s</p></div>',
            esc_attr($class),
            esc_html(sprintf(__('PostcodeAPI.nu verbruik: %1$d van %2$d aanvragen deze maand (%3$d%%).', 'tempel-settings'), $count, $limit, $percentage))
        );
    }

    private function get_cache_key(string $postcode, int $huisnummer): string
    {
        return 'tmpl_gf_bag_address_' . md5($postcode . '|' . $huisnummer);
    }

    private function get_cached_address(string $cache_key): array
    {
        $cached = get_transient($cache_key);

        return is_array($cached) ? $cached : array();
    }

    private function set_cached_address(string $cache_key, array $data, int $cache_days): void
    {
        if ($cache_days <= 0) {
            return;
        }

        unset($data['toevoeging']);

        set_transient($cache_key, $data, $cache_days * DAY_IN_SECONDS);
    }

    private function check_rate_limit(int $limit): bool
    {
        $key = 'tmpl_gf_bag_rate_' . md5($this->get_rate_limit_identifier());
        $count = (int) get_transient($key);

        if ($count >= $limit) {
            return false;
        }

        set_transient($key, $count + 1, MINUTE_IN_SECONDS);

        return true;
    }

    private function get_rate_limit_identifier(): string
    {
        if (is_user_logged_in()) {
            return 'user_' . get_current_user_id();
        }

        $ip = isset($_SERVER['REMOTE_ADDR']) ? sanitize_text_field(wp_unslash($_SERVER['REMOTE_ADDR'])) : 'unknown';

        return 'ip_' . $ip;
    }

    private function normalize_postcode(string $postcode): string
    {
        return strtoupper(str_replace(' ', '', trim($postcode)));
    }

    private function normalize_house_number(string $huisnummer): int
    {
        if (preg_match('/^\s*([0-9]+)/', $huisnummer, $matches)) {
            return absint($matches[1]);
        }

        return 0;
    }

    private function normalize_endpoint(string $endpoint): string
    {
        $endpoint = rtrim(trim($endpoint), '/');
        $endpoint = preg_replace('#/\{postcode\}/\{number\}$#', '', $endpoint);
        $endpoint = preg_replace('#/\{postcode\}/\{huisnummer\}$#', '', $endpoint);

        if (preg_match('#^https://(api|sandbox)\.postcodeapi\.nu/v3$#', $endpoint)) {
            $endpoint .= '/lookup';
        }

        return $endpoint ?: self::DEFAULT_ENDPOINT;
    }
}
