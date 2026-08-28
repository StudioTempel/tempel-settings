<?php

namespace Tempel;

class Status_Monitor
{
    private const MONITOR_HOOK = 'tempel_status_health_check';
    private const ENDPOINTS_OPTION = 'tempel_status_monitor_endpoints';
    private const STATUS_OPTION = 'tempel_status_monitor_status';
    private const GRAVITY_FORMS_STATUS_OPTION = 'tempel_status_gravity_forms_status';

    public static function init(): void
    {
        add_action(self::MONITOR_HOOK, array(__CLASS__, 'run'));
        add_action('gform_after_submission', array(__CLASS__, 'record_gravity_forms_success'), 10, 2);
        add_filter('gform_validation', array(__CLASS__, 'record_gravity_forms_validation'));

        if (!wp_next_scheduled(self::MONITOR_HOOK)) {
            wp_schedule_event(time() + HOUR_IN_SECONDS, 'twicedaily', self::MONITOR_HOOK);
        }
    }

    public static function run(): array
    {
        $results = array();
        $has_error = false;

        foreach (self::get_endpoints() as $endpoint) {
            $host = (string) wp_parse_url($endpoint, PHP_URL_HOST);
            $response = wp_safe_remote_head($endpoint, array('timeout' => 8, 'redirection' => 2));

            if (is_wp_error($response)) {
                $message = sprintf(__('Controle van %s is mislukt.', 'tempel-settings'), $host);
                Status_Log::add('webhook', 'error', $message, array('host' => $host, 'error_code' => $response->get_error_code()));
                $results[] = array('host' => $host, 'status' => 'error', 'message' => $message);
                $has_error = true;
                continue;
            }

            $status_code = (int) wp_remote_retrieve_response_code($response);
            $is_error = $status_code < 200 || $status_code >= 400;
            $message = $is_error
                ? sprintf(__('Controle van %1$s gaf HTTP %2$d terug.', 'tempel-settings'), $host, $status_code)
                : sprintf(__('Controle van %s geslaagd.', 'tempel-settings'), $host);

            Status_Log::add('webhook', $is_error ? 'error' : 'success', $message, array('host' => $host, 'http_status' => $status_code));
            $results[] = array('host' => $host, 'status' => $is_error ? 'error' : 'success', 'message' => $message);
            $has_error = $has_error || $is_error;
        }

        $system_warnings = self::get_system_warnings();
        foreach ($system_warnings as $warning) {
            Status_Log::add('system', 'warning', $warning);
        }

        $status = array(
            'status' => $has_error || !empty($system_warnings) ? 'warning' : 'success',
            'timestamp' => time(),
            'message' => $has_error || !empty($system_warnings)
                ? __('De periodieke controle heeft aandachtspunten gevonden.', 'tempel-settings')
                : __('De periodieke controle is geslaagd.', 'tempel-settings'),
            'webhooks' => $results,
        );
        update_option(self::STATUS_OPTION, $status, false);

        return $status;
    }

    public static function get_status(): array
    {
        $status = get_option(self::STATUS_OPTION, array());
        return is_array($status) ? $status : array();
    }

    public static function get_gravity_forms_status(): array
    {
        $status = get_option(self::GRAVITY_FORMS_STATUS_OPTION, array());
        return is_array($status) ? $status : array();
    }

    public static function get_endpoints(): array
    {
        $endpoints = get_option(self::ENDPOINTS_OPTION, array());
        return is_array($endpoints) ? array_values($endpoints) : array();
    }

    public static function save_endpoints(array $endpoints): void
    {
        $safe = array();

        foreach (array_slice($endpoints, 0, 5) as $endpoint) {
            $endpoint = esc_url_raw(trim((string) $endpoint));

            if ($endpoint !== '' && wp_http_validate_url($endpoint) && wp_parse_url($endpoint, PHP_URL_SCHEME) === 'https') {
                $safe[] = $endpoint;
            }
        }

        update_option(self::ENDPOINTS_OPTION, array_values(array_unique($safe)), false);
    }

    public static function record_gravity_forms_success(array $entry, array $form): void
    {
        $form_id = absint($form['id'] ?? 0);
        $entry_id = absint($entry['id'] ?? 0);
        $message = sprintf(__('Gravity Forms-inzending voor formulier %d verwerkt.', 'tempel-settings'), $form_id);
        $status = array('status' => 'success', 'timestamp' => time(), 'message' => $message);

        update_option(self::GRAVITY_FORMS_STATUS_OPTION, $status, false);
        Status_Log::add('gravity_forms', 'success', $message, array('form_id' => $form_id, 'entry_id' => $entry_id));
    }

    public static function record_gravity_forms_validation(array $validation_result): array
    {
        if (!empty($validation_result['is_valid'])) {
            return $validation_result;
        }

        $form_id = absint($validation_result['form']['id'] ?? 0);
        $message = sprintf(__('Gravity Forms-validatie voor formulier %d is mislukt.', 'tempel-settings'), $form_id);
        $status = array('status' => 'warning', 'timestamp' => time(), 'message' => $message);

        update_option(self::GRAVITY_FORMS_STATUS_OPTION, $status, false);
        Status_Log::add('gravity_forms', 'warning', $message, array('form_id' => $form_id));

        return $validation_result;
    }

    private static function get_system_warnings(): array
    {
        $warnings = array();
        $memory = wp_convert_hr_to_bytes((string) ini_get('memory_limit'));

        if ($memory > 0 && $memory < 128 * MB_IN_BYTES) {
            $warnings[] = __('De PHP-geheugenlimiet is lager dan 128 MB.', 'tempel-settings');
        }

        if (!is_ssl()) {
            $warnings[] = __('WordPress-beheer draait niet via HTTPS.', 'tempel-settings');
        }

        if (defined('DISABLE_WP_CRON') && DISABLE_WP_CRON) {
            $warnings[] = __('WP-Cron is uitgeschakeld; controleer de servercron.', 'tempel-settings');
        }

        return $warnings;
    }
}
