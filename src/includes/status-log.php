<?php

namespace Tempel;

class Status_Log
{
    private const LOG_OPTION = 'tempel_status_log';
    private const MAIL_STATUS_OPTION = 'tempel_status_mail_status';
    private const API_STATUS_OPTION = 'tempel_status_api_status';
    private const CLEANUP_HOOK = 'tempel_status_log_cleanup';
    private const MAX_ENTRIES = 500;
    private const RETENTION_DAYS = 30;

    public static function init(): void
    {
        add_action('wp_mail_succeeded', array(__CLASS__, 'record_mail_success'), 10, 1);
        add_action('wp_mail_failed', array(__CLASS__, 'record_mail_failure'), 10, 1);
        add_action('http_api_debug', array(__CLASS__, 'record_http_request'), 10, 5);
        add_action(self::CLEANUP_HOOK, array(__CLASS__, 'cleanup'));

        if (!wp_next_scheduled(self::CLEANUP_HOOK)) {
            wp_schedule_event(time() + HOUR_IN_SECONDS, 'daily', self::CLEANUP_HOOK);
        }
    }

    public static function add(string $source, string $level, string $message, array $context = array()): void
    {
        $levels = array('success', 'warning', 'error', 'info');
        $level = in_array($level, $levels, true) ? $level : 'info';
        $logs = self::get_entries();

        array_unshift($logs, array(
            'id' => wp_generate_uuid4(),
            'timestamp' => time(),
            'source' => sanitize_key($source),
            'level' => $level,
            'message' => self::sanitize_message($message),
            'context' => self::sanitize_context($context),
        ));

        $logs = array_slice($logs, 0, self::MAX_ENTRIES);
        update_option(self::LOG_OPTION, $logs, false);
    }

    public static function get_entries(array $filters = array()): array
    {
        $logs = get_option(self::LOG_OPTION, array());
        $logs = is_array($logs) ? $logs : array();
        $cutoff = time() - (self::RETENTION_DAYS * DAY_IN_SECONDS);
        $level = isset($filters['level']) ? sanitize_key($filters['level']) : '';
        $source = isset($filters['source']) ? sanitize_key($filters['source']) : '';

        return array_values(array_filter($logs, static function ($entry) use ($cutoff, $level, $source): bool {
            if (!is_array($entry) || (int) ($entry['timestamp'] ?? 0) < $cutoff) {
                return false;
            }

            if ($level !== '' && ($entry['level'] ?? '') !== $level) {
                return false;
            }

            return $source === '' || ($entry['source'] ?? '') === $source;
        }));
    }

    public static function clear(): void
    {
        delete_option(self::LOG_OPTION);
    }

    public static function cleanup(): void
    {
        update_option(self::LOG_OPTION, self::get_entries(), false);
    }

    public static function get_mail_status(): array
    {
        $status = get_option(self::MAIL_STATUS_OPTION, array());
        return is_array($status) ? $status : array();
    }

    public static function get_api_status(): array
    {
        $status = get_option(self::API_STATUS_OPTION, array());
        return is_array($status) ? $status : array();
    }

    public static function record_mail_success(array $mail_data): void
    {
        $status = array('status' => 'success', 'timestamp' => time(), 'message' => __('E-mail geaccepteerd voor verzending.', 'tempel-settings'));
        update_option(self::MAIL_STATUS_OPTION, $status, false);
        self::add('mail', 'success', $status['message']);
    }

    public static function record_mail_failure(\WP_Error $error): void
    {
        $message = $error->get_error_message() ?: __('WordPress kon de e-mail niet versturen.', 'tempel-settings');
        $status = array('status' => 'error', 'timestamp' => time(), 'message' => self::sanitize_message($message));
        update_option(self::MAIL_STATUS_OPTION, $status, false);
        self::add('mail', 'error', $status['message'], array('error_code' => $error->get_error_code()));
    }

    public static function record_http_request($response, string $context, string $transport, array $args, string $url): void
    {
        if ($context !== 'response') {
            return;
        }

        $host = strtolower((string) wp_parse_url($url, PHP_URL_HOST));
        $allowed_hosts = apply_filters('tempel_status_log_api_hosts', array('api.postcodeapi.nu', 'sandbox.postcodeapi.nu'));

        if (!in_array($host, (array) $allowed_hosts, true)) {
            return;
        }

        if (is_wp_error($response)) {
            $message = $response->get_error_message() ?: __('De API-aanvraag is mislukt.', 'tempel-settings');
            $status = array('status' => 'error', 'timestamp' => time(), 'message' => self::sanitize_message($message));
            update_option(self::API_STATUS_OPTION, $status, false);
            self::add('api', 'error', $status['message'], array('host' => $host, 'error_code' => $response->get_error_code()));
            return;
        }

        $status_code = (int) wp_remote_retrieve_response_code($response);
        $is_error = $status_code >= 400;
        $message = $is_error
            ? sprintf(__('API-aanvraag gaf HTTP %d terug.', 'tempel-settings'), $status_code)
            : __('API-aanvraag geslaagd.', 'tempel-settings');
        $status = array('status' => $is_error ? 'error' : 'success', 'timestamp' => time(), 'message' => $message, 'status_code' => $status_code);

        update_option(self::API_STATUS_OPTION, $status, false);
        self::add('api', $status['status'], $message, array('host' => $host, 'http_status' => $status_code));
    }

    private static function sanitize_context(array $context): array
    {
        $safe = array();

        foreach ($context as $key => $value) {
            $key = sanitize_key((string) $key);

            if ($key === '' || preg_match('/pass|secret|token|auth|key|email|recipient|body|request|postcode|huisnummer/i', $key)) {
                continue;
            }

            if (is_bool($value) || is_int($value) || is_float($value)) {
                $safe[$key] = $value;
            } elseif (is_string($value)) {
                $safe[$key] = substr(self::sanitize_message($value), 0, 250);
            }
        }

        return $safe;
    }

    private static function sanitize_message(string $message): string
    {
        $message = sanitize_text_field($message);
        $message = preg_replace('/[A-Z0-9._%+\-]+@[A-Z0-9.\-]+\.[A-Z]{2,}/i', '[e-mail afgeschermd]', $message) ?? $message;
        $message = preg_replace('#(https?://[^\s?]+)\?[^\s]+#i', '$1?[parameters afgeschermd]', $message) ?? $message;
        $message = preg_replace('/(api[_\s-]?key|token|password|wachtwoord)\s*[:=]\s*[^\s,;]+/i', '$1=[afgeschermd]', $message) ?? $message;

        return substr($message, 0, 500);
    }
}
