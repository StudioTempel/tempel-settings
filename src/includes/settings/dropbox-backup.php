<?php

namespace Tempel;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Creates full WordPress backups and transfers them to Dropbox.
 */
class Dropbox_Backup
{
    public const SETTINGS_OPTION = 'tempel_dropbox_backup_settings';
    public const CREDENTIALS_OPTION = 'tempel_dropbox_backup_credentials';
    public const STATUS_OPTION = 'tempel_dropbox_backup_status';
    public const CRON_HOOK = 'tempel_dropbox_backup_run';
    public const SINGLE_HOOK = 'tempel_dropbox_backup_run_once';
    private const LOCK_KEY = 'tempel_dropbox_backup_lock';
    private const ACCESS_TOKEN_KEY = 'tempel_dropbox_access_token';
    private const CHUNK_SIZE = 8388608;

    public function __construct()
    {
        add_option(self::SETTINGS_OPTION, self::defaults(), '', false);
        add_filter('cron_schedules', array($this, 'cron_schedules'));
        add_action('wp_loaded', array($this, 'sync_schedule'));
        add_action(self::CRON_HOOK, array($this, 'run'));
        add_action(self::SINGLE_HOOK, array($this, 'run'));
        add_action('admin_post_tempel_dropbox_connect', array($this, 'connect'));
        add_action('admin_post_tempel_dropbox_callback', array($this, 'callback'));
        add_action('admin_post_tempel_dropbox_disconnect', array($this, 'disconnect'));
        add_action('admin_post_tempel_dropbox_backup_now', array($this, 'run_now'));
        add_action('wp_ajax_tempel_dropbox_list_folders', array($this, 'list_folders_ajax'));
    }

    public static function defaults(): array
    {
        return array(
            'enabled' => '',
            'frequency' => 'weekly',
            'time' => '03:30',
            'retention' => 4,
            'folder' => '/Tempel Backups',
            'app_key' => '',
            'app_secret' => '',
            'notification_email' => 'info@studiotempel.nl',
            'notify_success' => '',
        );
    }

    public static function settings(): array
    {
        $settings = get_option(self::SETTINGS_OPTION, array());
        return wp_parse_args(is_array($settings) ? $settings : array(), self::defaults());
    }

    public static function status(): array
    {
        $status = get_option(self::STATUS_OPTION, array());
        return is_array($status) ? $status : array();
    }

    public static function is_connected(): bool
    {
        $credentials = get_option(self::CREDENTIALS_OPTION, array());
        return is_array($credentials) && !empty($credentials['refresh_token']);
    }

    public function cron_schedules(array $schedules): array
    {
        $schedules['tempel_monthly'] = array(
            'interval' => 30 * DAY_IN_SECONDS,
            'display' => __('Iedere 30 dagen', 'tempel-settings'),
        );
        return $schedules;
    }

    public function sync_schedule(): void
    {
        if (is_multisite() && !is_main_site()) {
            $this->clear_schedule();
            return;
        }

        $settings = self::settings();
        $frequency = $settings['frequency'] === 'monthly' ? 'tempel_monthly' : 'weekly';
        $scheduled = wp_get_scheduled_event(self::CRON_HOOK);
        $should_run = $settings['enabled'] === 'on' && self::is_connected();

        if (!$should_run) {
            $this->clear_schedule();
            return;
        }

        $scheduled_time = $scheduled ? wp_date('H:i', (int) $scheduled->timestamp, wp_timezone()) : '';
        if ($scheduled && $scheduled->schedule === $frequency && $scheduled_time === $settings['time']) {
            return;
        }

        $this->clear_schedule();
        wp_schedule_event($this->next_run_timestamp((string) $settings['time']), $frequency, self::CRON_HOOK);
    }

    public function connect(): void
    {
        $this->guard_admin_action('tempel_dropbox_connect');
        $settings = self::settings();

        if (empty($settings['app_key']) || empty($settings['app_secret'])) {
            $this->redirect('missing_app');
        }

        $state = wp_generate_password(40, false, false);
        set_transient('tempel_dropbox_oauth_' . get_current_user_id(), $state, 15 * MINUTE_IN_SECONDS);
        $url = add_query_arg(array(
            'client_id' => $settings['app_key'],
            'response_type' => 'code',
            'token_access_type' => 'offline',
            'redirect_uri' => self::callback_url(),
            'state' => $state,
        ), 'https://www.dropbox.com/oauth2/authorize');
        wp_redirect($url);
        exit;
    }

    public function callback(): void
    {
        if (!current_user_can('manage_options')) {
            wp_die(esc_html__('Je hebt geen rechten om Dropbox te koppelen.', 'tempel-settings'));
        }

        $state = isset($_GET['state']) ? sanitize_text_field(wp_unslash($_GET['state'])) : '';
        $expected = (string) get_transient('tempel_dropbox_oauth_' . get_current_user_id());
        delete_transient('tempel_dropbox_oauth_' . get_current_user_id());

        if ($expected === '' || !hash_equals($expected, $state)) {
            $this->redirect('oauth_state');
        }

        $code = isset($_GET['code']) ? sanitize_text_field(wp_unslash($_GET['code'])) : '';
        $settings = self::settings();
        $response = wp_remote_post('https://api.dropboxapi.com/oauth2/token', array(
            'timeout' => 30,
            'body' => array(
                'code' => $code,
                'grant_type' => 'authorization_code',
                'client_id' => $settings['app_key'],
                'client_secret' => $settings['app_secret'],
                'redirect_uri' => self::callback_url(),
            ),
        ));
        $body = $this->decode_response($response, 200, __('Dropbox-koppeling mislukt.', 'tempel-settings'));

        if (is_wp_error($body) || empty($body['refresh_token'])) {
            $this->set_status('error', is_wp_error($body) ? $body->get_error_message() : __('Dropbox gaf geen refresh-token terug.', 'tempel-settings'));
            $this->redirect('oauth_failed');
        }

        update_option(self::CREDENTIALS_OPTION, array(
            'refresh_token' => (string) $body['refresh_token'],
            'account_id' => sanitize_text_field((string) ($body['account_id'] ?? '')),
            'connected_at' => time(),
        ), false);
        $this->set_status('connected', __('Dropbox is gekoppeld. Er is nog geen back-up gemaakt.', 'tempel-settings'));
        $this->sync_schedule();
        $this->redirect('connected');
    }

    public function disconnect(): void
    {
        $this->guard_admin_action('tempel_dropbox_disconnect');
        delete_option(self::CREDENTIALS_OPTION);
        delete_transient(self::ACCESS_TOKEN_KEY);
        $this->clear_schedule();
        $this->set_status('disconnected', __('Dropbox is ontkoppeld.', 'tempel-settings'));
        $this->redirect('disconnected');
    }

    public function run_now(): void
    {
        $this->guard_admin_action('tempel_dropbox_backup_now');

        if (!self::is_connected()) {
            $this->redirect('not_connected');
        }

        if (!wp_next_scheduled(self::SINGLE_HOOK)) {
            $this->set_status('queued', __('De handmatige back-up staat klaar en begint op de achtergrond.', 'tempel-settings'));
            wp_schedule_single_event(time() + 5, self::SINGLE_HOOK);
            spawn_cron();
        }
        $this->redirect('queued');
    }

    public function list_folders_ajax(): void
    {
        check_ajax_referer('tempel_dropbox_folder_browser', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => __('Je hebt geen rechten om Dropbox-mappen te bekijken.', 'tempel-settings')), 403);
        }

        if (!self::is_connected()) {
            wp_send_json_error(array('message' => __('Dropbox is niet gekoppeld.', 'tempel-settings')), 400);
        }

        $path = isset($_POST['path']) ? sanitize_text_field(wp_unslash($_POST['path'])) : '';
        $path = $path === '/' ? '' : '/' . trim($path, '/');

        try {
            $result = $this->rpc_request('/2/files/list_folder', $this->access_token(), array(
                'path' => $path,
                'recursive' => false,
                'include_deleted' => false,
                'include_non_downloadable_files' => false,
                'limit' => 2000,
            ));
            $folders = array();

            foreach ((array) ($result['entries'] ?? array()) as $entry) {
                if (($entry['.tag'] ?? '') !== 'folder') {
                    continue;
                }
                $folders[] = array(
                    'name' => sanitize_text_field((string) ($entry['name'] ?? '')),
                    'path' => sanitize_text_field((string) ($entry['path_display'] ?? $entry['path_lower'] ?? '')),
                );
            }

            usort($folders, static fn($a, $b) => strnatcasecmp($a['name'], $b['name']));
            wp_send_json_success(array(
                'path' => $path === '' ? '/' : $path,
                'parent' => $this->parent_dropbox_path($path),
                'folders' => $folders,
            ));
        } catch (\Throwable $error) {
            wp_send_json_error(array('message' => sanitize_text_field($error->getMessage())), 500);
        }
    }

    public function run(): void
    {
        if ((is_multisite() && !is_main_site()) || get_transient(self::LOCK_KEY)) {
            return;
        }

        set_transient(self::LOCK_KEY, 1, 12 * HOUR_IN_SECONDS);
        $archive = '';
        $dump = '';

        try {
            if (!class_exists('ZipArchive')) {
                throw new \RuntimeException(__('De PHP-extensie ZipArchive ontbreekt.', 'tempel-settings'));
            }
            if (!self::is_connected()) {
                throw new \RuntimeException(__('Dropbox is niet gekoppeld.', 'tempel-settings'));
            }

            $this->set_status('running', __('Back-up wordt samengesteld.', 'tempel-settings'));
            if (function_exists('set_time_limit')) {
                @set_time_limit(0);
            }

            $work_dir = trailingslashit(get_temp_dir()) . 'tempel-backup-' . wp_generate_uuid4();
            if (!wp_mkdir_p($work_dir)) {
                throw new \RuntimeException(__('De tijdelijke back-upmap kon niet worden gemaakt.', 'tempel-settings'));
            }

            $dump = $work_dir . '/database.sql';
            $this->dump_database($dump);
            $filename = sanitize_file_name(wp_parse_url(home_url('/'), PHP_URL_HOST) . '-' . gmdate('Y-m-d-His') . '.zip');
            $archive = $work_dir . '/' . $filename;
            $this->create_archive($archive, $dump);
            $size = (int) filesize($archive);

            $this->set_status('uploading', sprintf(__('Back-up van %s wordt naar Dropbox verstuurd.', 'tempel-settings'), size_format($size)));
            $remote_path = $this->remote_site_folder() . '/' . $filename;
            $this->ensure_remote_folder();
            $this->upload($archive, $remote_path);
            $this->apply_retention();

            $this->set_status('success', __('Volledige back-up is naar Dropbox verstuurd.', 'tempel-settings'), array(
                'file' => $filename,
                'bytes' => $size,
                'remote_path' => $remote_path,
            ));
            if (class_exists('Tempel\\Status_Log')) {
                Status_Log::add('backup', 'success', __('Dropbox-back-up voltooid.', 'tempel-settings'), array('bytes' => $size));
            }
            if (self::settings()['notify_success'] === 'on') {
                $this->send_notification(true, __('De volledige back-up is succesvol naar Dropbox verstuurd.', 'tempel-settings'), $filename, $size);
            }
        } catch (\Throwable $error) {
            $this->set_status('error', $error->getMessage());
            if (class_exists('Tempel\\Status_Log')) {
                Status_Log::add('backup', 'error', $error->getMessage());
            }
            $this->send_notification(false, $error->getMessage());
        } finally {
            if ($archive !== '') {
                $work_dir = dirname($archive);
                $this->remove_tree($work_dir);
            } elseif ($dump !== '') {
                $this->remove_tree(dirname($dump));
            }
            delete_transient(self::LOCK_KEY);
        }
    }

    private function dump_database(string $path): void
    {
        global $wpdb;
        $handle = fopen($path, 'wb');
        if (!$handle) {
            throw new \RuntimeException(__('Het databasebestand kon niet worden geschreven.', 'tempel-settings'));
        }

        fwrite($handle, "-- Tempel Settings WordPress backup\nSET FOREIGN_KEY_CHECKS=0;\nSET SQL_MODE='NO_AUTO_VALUE_ON_ZERO';\n\n");
        $tables = $wpdb->get_results('SHOW FULL TABLES', ARRAY_N);

        foreach ((array) $tables as $table_info) {
            $table = (string) ($table_info[0] ?? '');
            $table_type = strtoupper((string) ($table_info[1] ?? 'BASE TABLE'));
            if ($table === '') {
                continue;
            }
            $quoted = '`' . str_replace('`', '``', $table) . '`';
            $create = $wpdb->get_row('SHOW CREATE TABLE ' . $quoted, ARRAY_N);
            if (!is_array($create) || empty($create[1])) {
                continue;
            }
            $drop_type = $table_type === 'VIEW' ? 'VIEW' : 'TABLE';
            fwrite($handle, "DROP {$drop_type} IF EXISTS {$quoted};\n{$create[1]};\n\n");
            if ($table_type === 'VIEW') {
                continue;
            }
            $offset = 0;
            do {
                $rows = $wpdb->get_results("SELECT * FROM {$quoted} LIMIT 500 OFFSET {$offset}", ARRAY_A);
                foreach ((array) $rows as $row) {
                    $columns = array_map(static fn($column) => '`' . str_replace('`', '``', (string) $column) . '`', array_keys($row));
                    $values = array_map(array($this, 'sql_value'), array_values($row));
                    fwrite($handle, 'INSERT INTO ' . $quoted . ' (' . implode(',', $columns) . ') VALUES (' . implode(',', $values) . ");\n");
                }
                $count = count((array) $rows);
                $offset += $count;
            } while ($count === 500);
            fwrite($handle, "\n");
        }
        fwrite($handle, "SET FOREIGN_KEY_CHECKS=1;\n");
        fclose($handle);
    }

    private function sql_value($value): string
    {
        global $wpdb;
        if ($value === null) {
            return 'NULL';
        }
        $value = (string) $value;
        if (isset($wpdb->dbh) && $wpdb->dbh instanceof \mysqli) {
            return "'" . mysqli_real_escape_string($wpdb->dbh, $value) . "'";
        }
        return "'" . addslashes($value) . "'";
    }

    private function create_archive(string $archive_path, string $dump_path): void
    {
        $zip = new \ZipArchive();
        if ($zip->open($archive_path, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) !== true) {
            throw new \RuntimeException(__('Het zipbestand kon niet worden gemaakt.', 'tempel-settings'));
        }

        $zip->addFile($dump_path, 'database.sql');
        $zip->addFromString('tempel-backup.json', wp_json_encode(array(
            'site_url' => home_url('/'),
            'created_at' => gmdate('c'),
            'wordpress' => get_bloginfo('version'),
            'php' => PHP_VERSION,
            'multisite' => is_multisite(),
        ), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

        $root = wp_normalize_path(ABSPATH);
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveCallbackFilterIterator(
                new \RecursiveDirectoryIterator($root, \FilesystemIterator::SKIP_DOTS),
                function (\SplFileInfo $file) use ($root): bool {
                    $path = wp_normalize_path($file->getPathname());
                    $relative = ltrim(substr($path, strlen($root)), '/');
                    return !$this->is_excluded($relative, $file->isDir()) && !$file->isLink();
                }
            ),
            \RecursiveIteratorIterator::LEAVES_ONLY
        );

        foreach ($iterator as $file) {
            if (!$file instanceof \SplFileInfo || !$file->isFile() || !$file->isReadable()) {
                continue;
            }
            $path = wp_normalize_path($file->getPathname());
            if (in_array($path, array(wp_normalize_path($archive_path), wp_normalize_path($dump_path)), true)) {
                continue;
            }
            $relative = ltrim(substr($path, strlen($root)), '/');
            $zip->addFile($path, 'site/' . $relative);
        }

        $parent_config = dirname(untrailingslashit($root)) . '/wp-config.php';
        if (!file_exists($root . 'wp-config.php') && is_readable($parent_config)) {
            $zip->addFile($parent_config, 'site/wp-config.php');
        }

        if (!$zip->close()) {
            throw new \RuntimeException(__('Het zipbestand kon niet worden afgerond.', 'tempel-settings'));
        }
    }

    private function is_excluded(string $relative, bool $is_directory): bool
    {
        $relative = trim(wp_normalize_path($relative), '/');
        $parts = explode('/', $relative);
        $basename = end($parts);
        $excluded_directories = array('.git', '.svn', 'node_modules', 'wp-content/cache', 'wp-content/upgrade', 'wp-content/ai1wm-backups', 'wp-content/updraft', 'wp-content/backup-db', 'wp-content/uploads/cache');
        foreach ($excluded_directories as $directory) {
            if ($relative === $directory || str_starts_with($relative, $directory . '/')) {
                return true;
            }
        }
        if ($is_directory && in_array($basename, array('.git', '.svn', 'node_modules'), true)) {
            return true;
        }
        return (bool) apply_filters('tempel_dropbox_backup_exclude', false, $relative, $is_directory);
    }

    private function upload(string $file, string $remote_path): void
    {
        $token = $this->access_token();
        $handle = fopen($file, 'rb');
        if (!$handle) {
            throw new \RuntimeException(__('Het back-upbestand kon niet worden gelezen.', 'tempel-settings'));
        }

        $size = (int) filesize($file);
        $response = $this->content_request('/2/files/upload_session/start', $token, array('close' => false), '');
        $session = (string) ($response['session_id'] ?? '');
        if ($session === '') {
            fclose($handle);
            throw new \RuntimeException(__('Dropbox startte geen uploadsessie.', 'tempel-settings'));
        }
        $offset = 0;

        while ($offset < $size) {
            $chunk = fread($handle, self::CHUNK_SIZE);
            if ($chunk === false) {
                fclose($handle);
                throw new \RuntimeException(__('Het back-upbestand kon niet volledig worden gelezen.', 'tempel-settings'));
            }
            $chunk_size = strlen($chunk);
            if (($offset + $chunk_size) >= $size) {
                $this->content_request('/2/files/upload_session/finish', $token, array(
                    'cursor' => array('session_id' => $session, 'offset' => $offset),
                    'commit' => array('path' => $remote_path, 'mode' => 'overwrite', 'autorename' => false, 'mute' => true),
                ), $chunk);
                $offset += $chunk_size;
                break;
            }
            $this->content_request('/2/files/upload_session/append_v2', $token, array(
                'cursor' => array('session_id' => $session, 'offset' => $offset),
                'close' => false,
            ), $chunk);
            $offset += $chunk_size;
        }
        fclose($handle);
    }

    private function apply_retention(): void
    {
        $settings = self::settings();
        $keep = max(1, min(24, (int) $settings['retention']));
        $token = $this->access_token();
        $result = $this->rpc_request('/2/files/list_folder', $token, array('path' => $this->remote_site_folder(), 'recursive' => false));
        $entries = array_filter((array) ($result['entries'] ?? array()), static fn($entry) => ($entry['.tag'] ?? '') === 'file' && str_ends_with((string) ($entry['name'] ?? ''), '.zip'));
        usort($entries, static fn($a, $b) => strcmp((string) ($b['server_modified'] ?? ''), (string) ($a['server_modified'] ?? '')));
        foreach (array_slice($entries, $keep) as $entry) {
            $this->rpc_request('/2/files/delete_v2', $token, array('path' => (string) $entry['path_lower']));
        }
    }

    private function ensure_remote_folder(): void
    {
        $token = $this->access_token();
        $current = '';

        foreach (explode('/', trim($this->remote_site_folder(), '/')) as $part) {
            if ($part === '') {
                continue;
            }
            $current .= '/' . $part;
            $response = wp_remote_post('https://api.dropboxapi.com/2/files/create_folder_v2', array(
                'timeout' => 60,
                'headers' => array('Authorization' => 'Bearer ' . $token, 'Content-Type' => 'application/json'),
                'body' => wp_json_encode(array('path' => $current, 'autorename' => false)),
                'data_format' => 'body',
            ));

            if (is_wp_error($response)) {
                throw new \RuntimeException($response->get_error_message());
            }

            $status = (int) wp_remote_retrieve_response_code($response);
            $body = (string) wp_remote_retrieve_body($response);
            if ($status !== 200 && !str_contains($body, 'conflict')) {
                throw new \RuntimeException(__('De Dropbox-back-upmap kon niet worden gemaakt.', 'tempel-settings'));
            }
        }
    }

    private function access_token(): string
    {
        $cached = get_transient(self::ACCESS_TOKEN_KEY);
        if (is_string($cached) && $cached !== '') {
            return $cached;
        }

        $settings = self::settings();
        $credentials = get_option(self::CREDENTIALS_OPTION, array());
        $response = wp_remote_post('https://api.dropboxapi.com/oauth2/token', array(
            'timeout' => 30,
            'body' => array(
                'grant_type' => 'refresh_token',
                'refresh_token' => (string) ($credentials['refresh_token'] ?? ''),
                'client_id' => $settings['app_key'],
                'client_secret' => $settings['app_secret'],
            ),
        ));
        $body = $this->decode_response($response, 200, __('Dropbox kon geen toegangstoken maken.', 'tempel-settings'));
        if (is_wp_error($body) || empty($body['access_token'])) {
            throw new \RuntimeException(is_wp_error($body) ? $body->get_error_message() : __('Dropbox gaf geen toegangstoken terug.', 'tempel-settings'));
        }
        $token = (string) $body['access_token'];
        $lifetime = max(60, min(3600, (int) ($body['expires_in'] ?? 14400) - 300));
        set_transient(self::ACCESS_TOKEN_KEY, $token, $lifetime);
        return $token;
    }

    private function content_request(string $route, string $token, array $arguments, string $body): array
    {
        $response = wp_remote_post('https://content.dropboxapi.com' . $route, array(
            'timeout' => 180,
            'headers' => array(
                'Authorization' => 'Bearer ' . $token,
                'Content-Type' => 'application/octet-stream',
                'Dropbox-API-Arg' => wp_json_encode($arguments),
            ),
            'body' => $body,
            'data_format' => 'body',
        ));
        $decoded = $this->decode_response($response, 200, __('Dropbox-upload mislukt.', 'tempel-settings'));
        if (is_wp_error($decoded)) {
            throw new \RuntimeException($decoded->get_error_message());
        }
        return $decoded;
    }

    private function rpc_request(string $route, string $token, array $arguments): array
    {
        $response = wp_remote_post('https://api.dropboxapi.com' . $route, array(
            'timeout' => 60,
            'headers' => array('Authorization' => 'Bearer ' . $token, 'Content-Type' => 'application/json'),
            'body' => wp_json_encode($arguments),
            'data_format' => 'body',
        ));
        $decoded = $this->decode_response($response, 200, __('Dropbox-aanvraag mislukt.', 'tempel-settings'));
        if (is_wp_error($decoded)) {
            throw new \RuntimeException($decoded->get_error_message());
        }
        return $decoded;
    }

    private function decode_response($response, int $expected, string $fallback)
    {
        if (is_wp_error($response)) {
            return $response;
        }
        $status = (int) wp_remote_retrieve_response_code($response);
        $body = json_decode((string) wp_remote_retrieve_body($response), true);
        if ($status !== $expected) {
            $summary = is_array($body) ? (string) ($body['error_summary'] ?? $body['error_description'] ?? '') : '';
            return new \WP_Error('tempel_dropbox_error', $summary !== '' ? $summary : $fallback);
        }
        return is_array($body) ? $body : array();
    }

    private function remote_site_folder(): string
    {
        $settings = self::settings();
        $base = '/' . trim((string) $settings['folder'], '/');
        $host = sanitize_title((string) wp_parse_url(home_url('/'), PHP_URL_HOST));
        return rtrim($base, '/') . '/' . ($host ?: 'wordpress-site');
    }

    private function parent_dropbox_path(string $path): string
    {
        $path = trim($path, '/');
        if ($path === '' || !str_contains($path, '/')) {
            return '/';
        }
        return '/' . dirname($path);
    }

    private function set_status(string $state, string $message, array $extra = array()): void
    {
        update_option(self::STATUS_OPTION, array_merge(array(
            'state' => $state,
            'message' => sanitize_text_field($message),
            'timestamp' => time(),
        ), $extra), false);
    }

    private function send_notification(bool $success, string $message, string $filename = '', int $bytes = 0): void
    {
        $settings = self::settings();
        $recipient = sanitize_email((string) $settings['notification_email']);
        if ($recipient === '' || !is_email($recipient)) {
            return;
        }

        $site_name = wp_specialchars_decode(get_bloginfo('name'), ENT_QUOTES);
        $subject = $success
            ? sprintf(__('[%s] Dropbox-back-up geslaagd', 'tempel-settings'), $site_name)
            : sprintf(__('[%s] Dropbox-back-up mislukt', 'tempel-settings'), $site_name);
        $lines = array(
            $success ? __('De automatische back-up is geslaagd.', 'tempel-settings') : __('De automatische back-up is mislukt.', 'tempel-settings'),
            '',
            sprintf(__('Website: %s', 'tempel-settings'), home_url('/')),
            sprintf(__('Tijdstip: %s', 'tempel-settings'), wp_date('d-m-Y H:i')),
            sprintf(__('Status: %s', 'tempel-settings'), sanitize_text_field($message)),
        );
        if ($filename !== '') {
            $lines[] = sprintf(__('Bestand: %s', 'tempel-settings'), sanitize_file_name($filename));
        }
        if ($bytes > 0) {
            $lines[] = sprintf(__('Grootte: %s', 'tempel-settings'), size_format($bytes));
        }

        wp_mail($recipient, $subject, implode("\n", $lines));
    }

    private function next_run_timestamp(string $time): int
    {
        if (!preg_match('/^(?:[01]\\d|2[0-3]):[0-5]\\d$/', $time)) {
            $time = '03:30';
        }
        [$hour, $minute] = array_map('intval', explode(':', $time));
        $now = new \DateTimeImmutable('now', wp_timezone());
        $next = $now->setTime($hour, $minute, 0);
        if ($next <= $now) {
            $next = $next->modify('+1 day');
        }
        return $next->getTimestamp();
    }

    private function clear_schedule(): void
    {
        $timestamp = wp_next_scheduled(self::CRON_HOOK);
        while ($timestamp) {
            wp_unschedule_event($timestamp, self::CRON_HOOK);
            $timestamp = wp_next_scheduled(self::CRON_HOOK);
        }
    }

    private function remove_tree(string $directory): void
    {
        if (!is_dir($directory)) {
            return;
        }
        $iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($directory, \FilesystemIterator::SKIP_DOTS), \RecursiveIteratorIterator::CHILD_FIRST);
        foreach ($iterator as $item) {
            $item->isDir() ? @rmdir($item->getPathname()) : @unlink($item->getPathname());
        }
        @rmdir($directory);
    }

    private function guard_admin_action(string $nonce): void
    {
        if (!current_user_can('manage_options')) {
            wp_die(esc_html__('Je hebt geen rechten voor deze actie.', 'tempel-settings'));
        }
        check_admin_referer($nonce);
    }

    private function redirect(string $notice): void
    {
        wp_safe_redirect(add_query_arg('backup_notice', sanitize_key($notice), admin_url('admin.php?page=tempel-backup-settings')));
        exit;
    }

    public static function callback_url(): string
    {
        return admin_url('admin-post.php?action=tempel_dropbox_callback');
    }
}
