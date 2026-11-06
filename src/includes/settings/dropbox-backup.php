<?php

namespace Tempel;

if (!defined('ABSPATH')) {
    exit;
}

if (!class_exists('Tempel\\Core\\SecretStore')) {
    require_once dirname(__DIR__, 2) . '/Core/SecretStore.php';
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
    public const UPLOAD_HOOK = 'tempel_dropbox_backup_upload_step';
    public const UPLOAD_STATE_OPTION = 'tempel_dropbox_backup_upload_state';
    private const LOCK_KEY = 'tempel_dropbox_backup_lock';
    private const WORKER_LOCK_KEY = 'tempel_dropbox_backup_upload_worker_lock';
    private const ACCESS_TOKEN_KEY = 'tempel_dropbox_access_token';
    private const CHUNK_SIZE = 8388608;
    private const CHUNKS_PER_STEP = 8;
    private const MAX_UPLOAD_RETRIES = 5;
    private const ORPHAN_MAX_AGE = 86400;

    public function __construct()
    {
        add_option(self::SETTINGS_OPTION, self::defaults(), '', false);
        $this->encrypt_legacy_credentials();
        add_filter('cron_schedules', array($this, 'cron_schedules'));
        add_action('wp_loaded', array($this, 'sync_schedule'));
        add_action(self::CRON_HOOK, array($this, 'run'));
        add_action(self::SINGLE_HOOK, array($this, 'run'));
        add_action(self::UPLOAD_HOOK, array($this, 'process_upload'));
        add_action('wp_loaded', array($this, 'recover_interrupted_upload'), 2);
        add_action('wp_loaded', array($this, 'cleanup_orphaned_work_directories'), 3);
        add_action('admin_post_tempel_dropbox_connect', array($this, 'connect'));
        add_action('admin_post_tempel_dropbox_callback', array($this, 'callback'));
        add_action('admin_post_tempel_dropbox_disconnect', array($this, 'disconnect'));
        add_action('admin_post_tempel_dropbox_backup_now', array($this, 'run_now'));
        add_action('wp_ajax_tempel_dropbox_list_folders', array($this, 'list_folders_ajax'));
        add_action('wp_ajax_tempel_dropbox_backup_status', array($this, 'status_ajax'));
        add_action('wp_ajax_tempel_dropbox_upload_worker', array($this, 'upload_worker_ajax'));
        add_action('wp_ajax_nopriv_tempel_dropbox_upload_worker', array($this, 'upload_worker_ajax'));
    }

    private function encrypt_legacy_credentials(): void
    {
        $settings = get_option(self::SETTINGS_OPTION, array());
        if (is_array($settings) && !empty($settings['app_secret']) && !\Tempel\Core\SecretStore::isEncrypted((string) $settings['app_secret'])) {
            $settings['app_secret'] = \Tempel\Core\SecretStore::encrypt((string) $settings['app_secret']);
            update_option(self::SETTINGS_OPTION, $settings, false);
        }

        $credentials = get_option(self::CREDENTIALS_OPTION, array());
        if (is_array($credentials) && !empty($credentials['refresh_token']) && !\Tempel\Core\SecretStore::isEncrypted((string) $credentials['refresh_token'])) {
            $credentials['refresh_token'] = \Tempel\Core\SecretStore::encrypt((string) $credentials['refresh_token']);
            update_option(self::CREDENTIALS_OPTION, $credentials, false);
        }
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
        $settings = wp_parse_args(is_array($settings) ? $settings : array(), self::defaults());
        $settings['app_secret'] = \Tempel\Core\SecretStore::decrypt((string) $settings['app_secret']);
        return $settings;
    }

    public static function status(): array
    {
        $status = get_option(self::STATUS_OPTION, array());
        return is_array($status) ? $status : array();
    }

    public static function is_connected(): bool
    {
        $credentials = get_option(self::CREDENTIALS_OPTION, array());
        return is_array($credentials) && \Tempel\Core\SecretStore::decrypt((string) ($credentials['refresh_token'] ?? '')) !== '';
    }

    public static function app_is_configured(): bool
    {
        $settings = self::settings();
        return trim((string) ($settings['app_key'] ?? '')) !== ''
            && trim((string) ($settings['app_secret'] ?? '')) !== '';
    }

    /** List portable Tempel site packages across all site folders in the configured Dropbox root. */
    public function portable_packages(): array
    {
        if (!self::is_connected()) { return array(); }
        $base = '/' . trim((string) self::settings()['folder'], '/');
        $result = $this->rpc_request('/2/files/list_folder', $this->access_token(), array(
            'path' => $base === '/' ? '' : $base,
            'recursive' => true,
            'include_deleted' => false,
            'include_non_downloadable_files' => false,
            'limit' => 2000,
        ));
        $packages = array();
        foreach ((array) ($result['entries'] ?? array()) as $entry) {
            $entry_name = (string) ($entry['name'] ?? '');
            if (($entry['.tag'] ?? '') !== 'file' || !str_starts_with($entry_name, 'tempel-sitepakket-') || strtolower(pathinfo($entry_name, PATHINFO_EXTENSION)) !== 'zip') { continue; }
            $packages[] = array(
                'name' => sanitize_file_name((string) $entry['name']),
                'path' => sanitize_text_field((string) ($entry['path_display'] ?? $entry['path_lower'] ?? '')),
                'size' => max(0, (int) ($entry['size'] ?? 0)),
                'modified' => sanitize_text_field((string) ($entry['server_modified'] ?? '')),
            );
        }
        usort($packages, static fn($a, $b) => strcmp($b['modified'], $a['modified']));
        return $packages;
    }

    /** Download a selected Dropbox package to a private temporary file for migration. */
    public function download_portable_package(string $path): string
    {
        $path = '/' . trim($path, '/');
        $base = '/' . trim((string) self::settings()['folder'], '/');
        if ($path === '/' || !str_starts_with(strtolower($path), strtolower(rtrim($base, '/') . '/')) || !str_ends_with(strtolower($path), '.zip')) {
            throw new \RuntimeException(__('Ongeldig Dropbox-sitepakket.', 'tempel-settings'));
        }
        $temporary = wp_tempnam('tempel-dropbox-package.zip');
        if (!$temporary) { throw new \RuntimeException(__('Kan geen tijdelijk bestand voor het Dropbox-pakket maken.', 'tempel-settings')); }
        @chmod($temporary, 0600);
        $response = wp_remote_get('https://content.dropboxapi.com/2/files/download', array(
            'timeout' => 900,
            'stream' => true,
            'filename' => $temporary,
            'headers' => array(
                'Authorization' => 'Bearer ' . $this->access_token(),
                'Dropbox-API-Arg' => wp_json_encode(array('path' => $path)),
            ),
        ));
        if (is_wp_error($response) || wp_remote_retrieve_response_code($response) !== 200 || !is_file($temporary) || filesize($temporary) <= 0) {
            @unlink($temporary);
            throw new \RuntimeException(is_wp_error($response) ? $response->get_error_message() : __('Dropbox-download mislukt.', 'tempel-settings'));
        }
        return $temporary;
    }

    /** Return the current job state for the live admin progress component. */
    public function status_ajax(): void
    {
        check_ajax_referer('tempel_dropbox_backup_status', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => __('Geen toegang.', 'tempel-settings')), 403);
        }

        $status = self::status();
        $state = sanitize_key((string) ($status['state'] ?? 'idle'));
        $allowed_states = array('idle', 'queued', 'running', 'uploading', 'success', 'error', 'connected', 'disconnected');
        if (!in_array($state, $allowed_states, true)) {
            $state = 'idle';
        }

        wp_send_json_success(array(
            'state' => $state,
            'active' => in_array($state, array('queued', 'running', 'uploading'), true),
            'message' => sanitize_text_field((string) ($status['message'] ?? '')),
            'progress' => max(0, min(100, (int) ($status['progress'] ?? 0))),
            'file' => sanitize_file_name((string) ($status['file'] ?? '')),
            'timestamp' => (int) ($status['timestamp'] ?? 0),
            'formattedTime' => !empty($status['timestamp']) ? wp_date('d-m-Y H:i', (int) $status['timestamp']) : '',
        ));
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
            'refresh_token' => \Tempel\Core\SecretStore::encrypt((string) $body['refresh_token']),
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
        $this->cleanup_upload_state();
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

        $status = self::status();
        if (get_transient(self::LOCK_KEY)
            || !empty($this->upload_state())
            || in_array((string) ($status['state'] ?? ''), array('queued', 'running', 'uploading'), true)) {
            $this->redirect('already_running');
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
        $handed_off = false;
        $migration = null;

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
            $this->assert_disk_space();
            if (!wp_mkdir_p($work_dir)) {
                throw new \RuntimeException(__('De tijdelijke back-upmap kon niet worden gemaakt.', 'tempel-settings'));
            }

            $filename = sanitize_file_name('tempel-sitepakket-' . wp_parse_url(home_url('/'), PHP_URL_HOST) . '-' . gmdate('Y-m-d-His') . '.zip');
            $archive = $work_dir . '/' . $filename;
            require_once TEMPEL_SETTINGS_DIR . 'src/includes/stager/class-stager-values.php';
            require_once TEMPEL_SETTINGS_DIR . 'src/includes/stager/class-stager-migration.php';
            $migration = new \Tempel_Stager_Migration();
            $migration->check();
            $package = $migration->export();
            if (!@rename($package, $archive) && !copy($package, $archive)) {
                throw new \RuntimeException(__('Het Tempel-sitepakket kon niet naar de uploadmap worden verplaatst.', 'tempel-settings'));
            }
            $migration->cleanup();
            $migration = null;
            $this->verify_archive($archive);
            $size = (int) filesize($archive);

            $this->set_status('uploading', sprintf(__('Back-up van %s wordt naar Dropbox verstuurd: 0%%.', 'tempel-settings'), size_format($size)), array(
                'file' => $filename,
                'bytes' => $size,
                'uploaded_bytes' => 0,
                'progress' => 0,
            ));
            $remote_path = $this->remote_site_folder() . '/' . $filename;
            $this->ensure_remote_folder();
            $session = $this->start_upload_session();
            update_option(self::UPLOAD_STATE_OPTION, array(
                'archive' => $archive,
                'work_dir' => $work_dir,
                'file' => $filename,
                'remote_path' => $remote_path,
                'session_id' => $session,
                'offset' => 0,
                'size' => $size,
                'started_at' => time(),
                'updated_at' => time(),
                'retry_count' => 0,
                'worker_token' => wp_generate_password(40, false, false),
            ), false);
            $handed_off = true;
            $this->schedule_upload_step(true);
        } catch (\Throwable $error) {
            if ($migration instanceof \Tempel_Stager_Migration) {
                $migration->cleanup();
            }
            $this->fail_backup($error->getMessage());
        } finally {
            if (!$handed_off) {
                if ($archive !== '') {
                    $work_dir = dirname($archive);
                    $this->remove_tree($work_dir);
                } elseif ($dump !== '') {
                    $this->remove_tree(dirname($dump));
                }
                delete_transient(self::LOCK_KEY);
            }
        }
    }

    public function upload_worker_ajax(): void
    {
        $state = $this->upload_state();
        $token = isset($_POST['token']) ? sanitize_text_field(wp_unslash($_POST['token'])) : '';
        $expected = (string) ($state['worker_token'] ?? '');

        if ($expected === '' || $token === '' || !hash_equals($expected, $token)) {
            status_header(403);
            exit;
        }

        $this->process_upload();
        status_header(204);
        exit;
    }

    public function process_upload(): void
    {
        $state = $this->upload_state();
        if (empty($state)) {
            $this->clear_upload_schedule();
            return;
        }

        if (get_transient(self::WORKER_LOCK_KEY)) {
            $this->schedule_upload_step(false, 300);
            return;
        }

        set_transient(self::WORKER_LOCK_KEY, 1, 10 * MINUTE_IN_SECONDS);

        try {
            if (function_exists('set_time_limit')) {
                @set_time_limit(0);
            }

            $archive = (string) ($state['archive'] ?? '');
            $size = (int) ($state['size'] ?? 0);
            $offset = (int) ($state['offset'] ?? 0);
            $session = (string) ($state['session_id'] ?? '');
            $remote_path = (string) ($state['remote_path'] ?? '');

            if (!$this->valid_upload_archive($archive, (string) ($state['work_dir'] ?? '')) || $size <= 0 || $session === '' || $remote_path === '') {
                throw new \RuntimeException(__('De tijdelijke back-up voor Dropbox ontbreekt of is ongeldig.', 'tempel-settings'));
            }

            $handle = fopen($archive, 'rb');
            if (!$handle || fseek($handle, $offset) !== 0) {
                throw new \RuntimeException(__('De Dropbox-upload kon niet worden hervat.', 'tempel-settings'));
            }

            $token = $this->access_token();
            $chunks = 0;

            while ($offset < $size && $chunks < self::CHUNKS_PER_STEP) {
                $chunk = fread($handle, self::CHUNK_SIZE);
                if ($chunk === false || $chunk === '') {
                    fclose($handle);
                    throw new \RuntimeException(__('Het back-upbestand kon niet volledig worden gelezen.', 'tempel-settings'));
                }

                $chunk_size = strlen($chunk);
                $is_final = ($offset + $chunk_size) >= $size;

                if ($is_final) {
                    $this->content_request('/2/files/upload_session/finish', $token, array(
                        'cursor' => array('session_id' => $session, 'offset' => $offset),
                        'commit' => array('path' => $remote_path, 'mode' => 'overwrite', 'autorename' => false, 'mute' => true),
                    ), $chunk);
                } else {
                    $this->content_request('/2/files/upload_session/append_v2', $token, array(
                        'cursor' => array('session_id' => $session, 'offset' => $offset),
                        'close' => false,
                    ), $chunk);
                }

                $offset += $chunk_size;
                $chunks++;
                $state['offset'] = $offset;
                $state['updated_at'] = time();
                $state['retry_count'] = 0;
                unset($state['last_error']);
                update_option(self::UPLOAD_STATE_OPTION, $state, false);
                $this->set_upload_progress($state);
            }

            fclose($handle);

            if ($offset >= $size) {
                $this->complete_backup($state);
                return;
            }

            $this->schedule_upload_step(true);
        } catch (\Throwable $error) {
            $state = $this->upload_state();
            if (!empty($state) && $this->remote_file_matches($state)) {
                $this->complete_backup($state);
            } elseif (!empty($state) && $this->can_retry_upload($state)) {
                $state['retry_count'] = (int) ($state['retry_count'] ?? 0) + 1;
                $state['updated_at'] = time();
                $state['last_error'] = sanitize_text_field($error->getMessage());
                update_option(self::UPLOAD_STATE_OPTION, $state, false);
                $delay = min(900, 30 * (2 ** max(0, $state['retry_count'] - 1)));
                $this->set_status('uploading', sprintf(
                    __('Dropbox is tijdelijk niet bereikbaar. Nieuwe poging %1$d van %2$d over ongeveer %3$d seconden.', 'tempel-settings'),
                    $state['retry_count'],
                    self::MAX_UPLOAD_RETRIES,
                    $delay
                ), array(
                    'file' => (string) ($state['file'] ?? ''),
                    'bytes' => (int) ($state['size'] ?? 0),
                    'uploaded_bytes' => (int) ($state['offset'] ?? 0),
                    'progress' => min(99, (int) floor(((int) ($state['offset'] ?? 0) / max(1, (int) ($state['size'] ?? 0))) * 100)),
                ));
                $this->clear_upload_schedule();
                $this->schedule_upload_step(false, $delay);
            } else {
                $this->fail_backup($error->getMessage());
                $this->cleanup_upload_state();
            }
        } finally {
            delete_transient(self::WORKER_LOCK_KEY);
        }
    }

    public function recover_interrupted_upload(): void
    {
        $state = $this->upload_state();
        if (!empty($state)) {
            $updated = (int) ($state['updated_at'] ?? 0);
            if ($updated > 0 && $updated < time() - (12 * HOUR_IN_SECONDS)) {
                $this->fail_backup(__('De Dropbox-upload is na twaalf uur gestopt en moet opnieuw worden gestart.', 'tempel-settings'));
                $this->cleanup_upload_state();
                return;
            }

            if (!wp_next_scheduled(self::UPLOAD_HOOK)) {
                $this->schedule_upload_step($updated < time() - (5 * MINUTE_IN_SECONDS));
            }
            return;
        }

        $status = self::status();
        if (in_array((string) ($status['state'] ?? ''), array('running', 'uploading'), true)
            && (int) ($status['timestamp'] ?? 0) < time() - (15 * MINUTE_IN_SECONDS)) {
            $this->fail_backup(__('De vorige back-up is door de server onderbroken. Start de back-up opnieuw met de hervatbare uploader.', 'tempel-settings'));
            delete_transient(self::LOCK_KEY);
        }
    }

    public function cleanup_orphaned_work_directories(): void
    {
        $base = realpath(get_temp_dir());
        if ($base === false || !is_dir($base)) {
            return;
        }

        $active = $this->upload_state();
        $active_dir = isset($active['work_dir']) ? realpath((string) $active['work_dir']) : false;
        foreach ((array) glob(trailingslashit($base) . 'tempel-backup-*', GLOB_ONLYDIR) as $directory) {
            $real = realpath($directory);
            if ($real === false || ($active_dir !== false && $real === $active_dir)) {
                continue;
            }
            $modified = @filemtime($real);
            if ($modified !== false && $modified < time() - self::ORPHAN_MAX_AGE && $this->valid_work_directory($real)) {
                $this->remove_tree($real);
            }
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

    private function verify_archive(string $archive_path): void
    {
        $zip = new \ZipArchive();
        $result = $zip->open($archive_path, \ZipArchive::CHECKCONS);
        if ($result !== true
            || $zip->locateName('database.sql') === false
            || $zip->locateName('tempel-backup.json') === false) {
            if ($result === true) {
                $zip->close();
            }
            throw new \RuntimeException(__('Het gemaakte back-upbestand is beschadigd of onvolledig.', 'tempel-settings'));
        }
        $zip->close();
    }

    private function assert_disk_space(): void
    {
        $free = @disk_free_space(get_temp_dir());
        if (!is_float($free) && !is_int($free)) {
            return;
        }

        $estimate = $this->estimated_source_size();
        $minimum = max(512 * MB_IN_BYTES, (int) ceil($estimate * 1.35));
        if ($free < $minimum) {
            throw new \RuntimeException(sprintf(
                __('Onvoldoende tijdelijke schijfruimte. Minimaal %1$s nodig, %2$s beschikbaar.', 'tempel-settings'),
                size_format($minimum),
                size_format((int) $free)
            ));
        }
    }

    private function estimated_source_size(): int
    {
        $total = 0;
        $root = wp_normalize_path(ABSPATH);
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveCallbackFilterIterator(
                new \RecursiveDirectoryIterator($root, \FilesystemIterator::SKIP_DOTS),
                function (\SplFileInfo $file) use ($root): bool {
                    $path = wp_normalize_path($file->getPathname());
                    $relative = ltrim(substr($path, strlen($root)), '/');
                    return !$file->isLink() && !$this->is_excluded($relative, $file->isDir());
                }
            ),
            \RecursiveIteratorIterator::LEAVES_ONLY
        );
        foreach ($iterator as $file) {
            if ($file instanceof \SplFileInfo && $file->isFile()) {
                $total += max(0, (int) $file->getSize());
            }
        }

        global $wpdb;
        $database = (int) $wpdb->get_var('SELECT COALESCE(SUM(data_length + index_length), 0) FROM information_schema.tables WHERE table_schema = DATABASE()');
        return $total + ($database * 2);
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

    private function start_upload_session(): string
    {
        $response = $this->content_request('/2/files/upload_session/start', $this->access_token(), array('close' => false), '');
        $session = (string) ($response['session_id'] ?? '');
        if ($session === '') {
            throw new \RuntimeException(__('Dropbox startte geen uploadsessie.', 'tempel-settings'));
        }
        return $session;
    }

    private function upload_state(): array
    {
        $state = get_option(self::UPLOAD_STATE_OPTION, array());
        return is_array($state) ? $state : array();
    }

    private function schedule_upload_step(bool $dispatch_now, int $delay = 60): void
    {
        if (!wp_next_scheduled(self::UPLOAD_HOOK)) {
            wp_schedule_single_event(time() + max(1, $delay), self::UPLOAD_HOOK);
        }

        if (!$dispatch_now) {
            return;
        }

        $state = $this->upload_state();
        $token = (string) ($state['worker_token'] ?? '');
        if ($token === '') {
            return;
        }

        wp_remote_post(admin_url('admin-ajax.php'), array(
            'timeout' => 0.01,
            'blocking' => false,
            'sslverify' => apply_filters('https_local_ssl_verify', false),
            'body' => array(
                'action' => 'tempel_dropbox_upload_worker',
                'token' => $token,
            ),
        ));
    }

    private function set_upload_progress(array $state): void
    {
        $size = max(1, (int) ($state['size'] ?? 0));
        $offset = min($size, max(0, (int) ($state['offset'] ?? 0)));
        $progress = min(99, (int) floor(($offset / $size) * 100));
        $this->set_status('uploading', sprintf(
            __('Back-up van %1$s wordt naar Dropbox verstuurd: %2$d%%.', 'tempel-settings'),
            size_format($size),
            $progress
        ), array(
            'file' => (string) ($state['file'] ?? ''),
            'bytes' => $size,
            'uploaded_bytes' => $offset,
            'progress' => $progress,
        ));
    }

    private function complete_backup(array $state): void
    {
        $filename = (string) ($state['file'] ?? '');
        $size = (int) ($state['size'] ?? 0);
        $remote_path = (string) ($state['remote_path'] ?? '');

        if (!$this->remote_file_matches($state)) {
            throw new \RuntimeException(__('Dropbox kon het geüploade bestand niet bevestigen.', 'tempel-settings'));
        }
        $this->set_status('success', __('Volledige back-up is naar Dropbox verstuurd.', 'tempel-settings'), array(
            'file' => $filename,
            'bytes' => $size,
            'uploaded_bytes' => $size,
            'progress' => 100,
            'remote_path' => $remote_path,
        ));

        if (class_exists('Tempel\\Status_Log')) {
            Status_Log::add('backup', 'success', __('Dropbox-back-up voltooid.', 'tempel-settings'), array('bytes' => $size));
        }
        if (self::settings()['notify_success'] === 'on') {
            $this->send_notification(true, __('De volledige back-up is succesvol naar Dropbox verstuurd.', 'tempel-settings'), $filename, $size);
        }

        try {
            $this->apply_retention();
        } catch (\Throwable $error) {
            if (class_exists('Tempel\\Status_Log')) {
                Status_Log::add('backup', 'warning', __('Back-up geslaagd, maar oude Dropbox-back-ups konden niet worden opgeschoond.', 'tempel-settings'), array(
                    'error' => sanitize_text_field($error->getMessage()),
                ));
            }
        }

        $this->cleanup_upload_state();
    }

    private function fail_backup(string $message): void
    {
        $message = sanitize_text_field($message);
        $this->set_status('error', $message);
        if (class_exists('Tempel\\Status_Log')) {
            Status_Log::add('backup', 'error', $message);
        }
        $this->send_notification(false, $message);
    }

    private function cleanup_upload_state(): void
    {
        $state = $this->upload_state();
        $work_dir = (string) ($state['work_dir'] ?? '');

        if ($work_dir !== '' && $this->valid_work_directory($work_dir)) {
            $this->remove_tree($work_dir);
        }

        delete_option(self::UPLOAD_STATE_OPTION);
        delete_transient(self::LOCK_KEY);
        delete_transient(self::WORKER_LOCK_KEY);
        $this->clear_upload_schedule();
    }

    private function clear_upload_schedule(): void
    {
        $timestamp = wp_next_scheduled(self::UPLOAD_HOOK);
        while ($timestamp) {
            wp_unschedule_event($timestamp, self::UPLOAD_HOOK);
            $timestamp = wp_next_scheduled(self::UPLOAD_HOOK);
        }
    }

    private function valid_upload_archive(string $archive, string $work_dir): bool
    {
        if (!$this->valid_work_directory($work_dir) || !is_readable($archive) || is_link($archive)) {
            return false;
        }

        $archive_real = realpath($archive);
        $work_real = realpath($work_dir);
        return $archive_real !== false && $work_real !== false
            && str_starts_with(wp_normalize_path($archive_real), trailingslashit(wp_normalize_path($work_real)));
    }

    private function valid_work_directory(string $work_dir): bool
    {
        $work_real = realpath($work_dir);
        $temp_real = realpath(get_temp_dir());
        if ($work_real === false || $temp_real === false || !is_dir($work_real) || is_link($work_real)) {
            return false;
        }

        return str_starts_with(
            trailingslashit(wp_normalize_path($work_real)),
            trailingslashit(wp_normalize_path($temp_real)) . 'tempel-backup-'
        );
    }

    private function can_retry_upload(array $state): bool
    {
        return (int) ($state['retry_count'] ?? 0) < self::MAX_UPLOAD_RETRIES
            && $this->valid_upload_archive((string) ($state['archive'] ?? ''), (string) ($state['work_dir'] ?? ''));
    }

    private function remote_file_matches(array $state): bool
    {
        $path = (string) ($state['remote_path'] ?? '');
        $size = (int) ($state['size'] ?? 0);
        if ($path === '' || $size <= 0) {
            return false;
        }

        try {
            $metadata = $this->rpc_request('/2/files/get_metadata', $this->access_token(), array(
                'path' => $path,
                'include_deleted' => false,
            ));
            return ($metadata['.tag'] ?? '') === 'file' && (int) ($metadata['size'] ?? -1) === $size;
        } catch (\Throwable $error) {
            return false;
        }
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
                'refresh_token' => \Tempel\Core\SecretStore::decrypt((string) ($credentials['refresh_token'] ?? '')),
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
