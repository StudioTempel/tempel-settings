<?php

namespace Tempel;

use Tempel\Core\SecretStore;
use WP_Error;

/** Connects this site to the central Tempel Hub without exposing the OpenAI key. */
final class Hub_Client
{
    public const OPTION = 'tempel_hub_connection';
    private const DEFAULT_URL = 'https://crm.studiotempel.nl';
    private const AUTO_PAIR_HOOK = 'tempel_hub_auto_pair';
    private const AUTO_PAIR_VERSION_OPTION = 'tempel_hub_auto_pair_version';
    private const AUTO_PAIR_ATTEMPT_OPTION = 'tempel_hub_auto_pair_attempt';
    private const AUTO_PAIR_RETRY_INTERVAL = 15 * MINUTE_IN_SECONDS;

    public function __construct()
    {
        add_action('admin_post_tempel_hub_pair', array($this, 'pair'));
        add_action('admin_post_tempel_hub_disconnect', array($this, 'disconnect'));
        add_action('rest_api_init', array($this, 'register_challenge_route'));
        add_action(self::AUTO_PAIR_HOOK, array($this, 'auto_pair'));

        // This object is itself created during `init`, so schedule immediately
        // instead of registering another callback on the action already running.
        $this->schedule_auto_pair();
    }

    public static function is_connected(): bool
    {
        $settings = self::settings();
        return $settings['site_id'] !== '' && $settings['site_secret'] !== '';
    }

    public static function is_enabled(): bool
    {
        $config = self::config();
        return self::is_connected() && (bool) ($config['enabled'] ?? true);
    }

    /** @return array<string, mixed> */
    public static function config(): array
    {
        $cached = get_transient('tempel_hub_config');
        if (is_array($cached)) return $cached;
        $stored = get_option(self::OPTION, array());
        $fallback = is_array($stored['config'] ?? null) ? $stored['config'] : array();
        if (!self::is_connected()) return $fallback;
        $response = self::signed_request('GET', '/api/tempel/v1/config', '', 15);
        if (!is_wp_error($response) && wp_remote_retrieve_response_code($response) === 200) {
            $body = json_decode(wp_remote_retrieve_body($response), true);
            if (is_array($body)) {
                set_transient('tempel_hub_config', $body, 5 * MINUTE_IN_SECONDS);
                return $body;
            }
        }
        return $fallback;
    }

    /** @return array{hub_url:string,site_id:string,site_secret:string,connected_at:int} */
    public static function settings(): array
    {
        $stored = get_option(self::OPTION, array());
        $stored = is_array($stored) ? $stored : array();
        return array(
            'hub_url' => esc_url_raw((string) ($stored['hub_url'] ?? self::DEFAULT_URL)),
            'site_id' => sanitize_text_field((string) ($stored['site_id'] ?? '')),
            'site_secret' => SecretStore::decrypt((string) ($stored['site_secret'] ?? '')),
            'connected_at' => absint($stored['connected_at'] ?? 0),
        );
    }

    public function pair(): void
    {
        if (!current_user_can('manage_options')) wp_die(esc_html__('Je hebt geen toestemming om deze koppeling te wijzigen.', 'tempel-settings'));
        check_admin_referer('tempel_hub_pair');
        $hub_url = esc_url_raw(untrailingslashit((string) wp_unslash($_POST['hub_url'] ?? self::DEFAULT_URL)));
        $code = sanitize_text_field(wp_unslash($_POST['pairing_code'] ?? ''));
        $response = wp_safe_remote_post($hub_url . '/api/tempel/v1/pair', array(
            'timeout' => 30,
            'redirection' => 0,
            'headers' => array('Content-Type' => 'application/json'),
            'body' => wp_json_encode(array(
                'pairing_code' => $code,
                'site_url' => home_url('/'),
                'plugin_version' => TEMPEL_SETTINGS_VERSION,
                'wordpress_version' => get_bloginfo('version'),
                'php_version' => PHP_VERSION,
            )),
        ));
        $redirect = admin_url('admin.php?page=tempel-ai-settings');
        if (is_wp_error($response)) {
            wp_safe_redirect(add_query_arg('tempel_hub_error', rawurlencode($response->get_error_message()), $redirect));
            exit;
        }
        $body = json_decode(wp_remote_retrieve_body($response), true);
        if (wp_remote_retrieve_response_code($response) !== 200 || !is_array($body) || empty($body['site_id']) || empty($body['site_secret'])) {
            $message = (string) ($body['error']['message'] ?? __('Koppelen met Tempel Hub is mislukt.', 'tempel-settings'));
            wp_safe_redirect(add_query_arg('tempel_hub_error', rawurlencode($message), $redirect));
            exit;
        }
        $this->store_connection($hub_url, $body);
        wp_safe_redirect(add_query_arg('tempel_hub_connected', '1', $redirect));
        exit;
    }

    public function disconnect(): void
    {
        if (!current_user_can('manage_options')) wp_die(esc_html__('Je hebt geen toestemming om deze koppeling te wijzigen.', 'tempel-settings'));
        check_admin_referer('tempel_hub_disconnect');
        delete_option(self::OPTION);
        delete_transient('tempel_hub_config');
        wp_safe_redirect(add_query_arg('tempel_hub_disconnected', '1', admin_url('admin.php?page=tempel-ai-settings')));
        exit;
    }

    /** @param array<string, mixed> $request */
    public static function responses(array $request, string $operation, int $timeout = 60)
    {
        $path = '/api/tempel/v1/responses';
        $body = wp_json_encode(array('operation' => sanitize_key($operation), 'request' => $request));
        return self::signed_request('POST', $path, $body, $timeout);
    }

    public function register_challenge_route(): void
    {
        register_rest_route('tempel/v1', '/hub-challenge', array('methods' => 'GET', 'callback' => array($this, 'challenge'), 'permission_callback' => '__return_true'));
    }

    public function challenge(\WP_REST_Request $request)
    {
        $token = sanitize_text_field((string) $request->get_param('token'));
        $expected = (string) get_transient('tempel_hub_pairing_challenge');
        if ($expected === '' || !hash_equals($expected, $token)) return new WP_Error('tempel_hub_invalid_challenge', __('Ongeldige domeincontrole.', 'tempel-settings'), array('status' => 404));
        delete_transient('tempel_hub_pairing_challenge');
        $host = preg_replace('/^www\./', '', strtolower((string) wp_parse_url(home_url('/'), PHP_URL_HOST))) ?? '';
        return rest_ensure_response(array('proof' => hash('sha256', $token . '|' . $host)));
    }

    public function schedule_auto_pair(): void
    {
        if (self::is_connected()) {
            return;
        }

        $paired_version = (string) get_option(self::AUTO_PAIR_VERSION_OPTION, '');
        if (!hash_equals($paired_version, TEMPEL_SETTINGS_VERSION)) {
            wp_clear_scheduled_hook(self::AUTO_PAIR_HOOK);
            delete_option(self::AUTO_PAIR_ATTEMPT_OPTION);
            update_option(self::AUTO_PAIR_VERSION_OPTION, TEMPEL_SETTINGS_VERSION, false);
        }

        if (wp_next_scheduled(self::AUTO_PAIR_HOOK)) {
            return;
        }

        $last_attempt = (int) get_option(self::AUTO_PAIR_ATTEMPT_OPTION, 0);
        if ($last_attempt > time() - self::AUTO_PAIR_RETRY_INTERVAL) {
            return;
        }

        wp_schedule_single_event(time(), self::AUTO_PAIR_HOOK);
    }

    public function auto_pair(): void
    {
        if (self::is_connected()) return;
        update_option(self::AUTO_PAIR_ATTEMPT_OPTION, time(), false);
        try {
            $challenge = bin2hex(random_bytes(32));
        } catch (\Throwable $exception) {
            return;
        }
        set_transient('tempel_hub_pairing_challenge', $challenge, 5 * MINUTE_IN_SECONDS);
        $hub_url = self::DEFAULT_URL;
        $response = wp_safe_remote_post($hub_url . '/api/tempel/v1/auto-pair', array(
            'timeout' => 30,
            'redirection' => 0,
            'headers' => array('Content-Type' => 'application/json'),
            'body' => wp_json_encode(array('site_url' => home_url('/'), 'challenge' => $challenge, 'plugin_version' => TEMPEL_SETTINGS_VERSION, 'wordpress_version' => get_bloginfo('version'), 'php_version' => PHP_VERSION)),
        ));
        delete_transient('tempel_hub_pairing_challenge');
        if (is_wp_error($response) || wp_remote_retrieve_response_code($response) !== 200) return;
        $body = json_decode(wp_remote_retrieve_body($response), true);
        if (!is_array($body) || empty($body['site_id']) || empty($body['site_secret'])) return;
        $this->store_connection($hub_url, $body);
    }

    /** @param array<string, mixed> $body */
    private function store_connection(string $hub_url, array $body): void
    {
        update_option(self::OPTION, array('hub_url' => $hub_url, 'site_id' => sanitize_text_field((string) $body['site_id']), 'site_secret' => SecretStore::encrypt((string) $body['site_secret']), 'connected_at' => time(), 'config' => is_array($body['config'] ?? null) ? $body['config'] : array()), false);
        if (is_array($body['config'] ?? null)) set_transient('tempel_hub_config', $body['config'], 5 * MINUTE_IN_SECONDS);
    }

    private static function signed_request(string $method, string $path, string $body, int $timeout)
    {
        $settings = self::settings();
        if ($settings['site_id'] === '' || $settings['site_secret'] === '') return new WP_Error('tempel_hub_not_connected', __('Deze website is niet gekoppeld aan Tempel Hub.', 'tempel-settings'));
        $timestamp = (string) time();
        $nonce = wp_generate_password(32, false, false);
        $canonical = implode("\n", array($timestamp, $nonce, strtoupper($method), $path, hash('sha256', $body)));
        $signature = hash_hmac('sha256', $canonical, $settings['site_secret']);

        $args = array(
            'timeout' => $timeout,
            'redirection' => 0,
            'headers' => array(
                'Content-Type' => 'application/json',
                'X-Tempel-Site' => $settings['site_id'],
                'X-Tempel-Timestamp' => $timestamp,
                'X-Tempel-Nonce' => $nonce,
                'X-Tempel-Signature' => $signature,
            ),
            'body' => $body,
        );
        $url = untrailingslashit($settings['hub_url']) . $path;
        return $method === 'GET' ? wp_safe_remote_get($url, $args) : wp_safe_remote_post($url, $args);
    }
}
