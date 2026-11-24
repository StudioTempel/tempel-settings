<?php

namespace Tempel;

if (!defined('ABSPATH')) {
    exit;
}

/** Integrates explicit, administrator-initiated site migrations. */
final class Stager
{
    public function __construct()
    {
        add_action('admin_init', array($this, 'deactivate_standalone_plugin'));
        add_action('admin_post_tempel_stager_export', array($this, 'export'));
        add_action('admin_post_tempel_stager_prepare_export', array($this, 'prepare_export'));
        add_action('admin_post_tempel_stager_download_export', array($this, 'download_export'));
        add_action('admin_post_tempel_stager_import', array($this, 'import'));
        add_action('admin_post_tempel_stager_deploy', array($this, 'deploy'));
        add_action('wp_ajax_tempel_stager_browse', array($this, 'browse'));
        add_action('wp_ajax_tempel_stager_dropbox_packages', array($this, 'dropbox_packages'));
        add_action('tempel_stager_cleanup_export', array($this, 'cleanup_export'));
    }

    public function deactivate_standalone_plugin(): void
    {
        if (!current_user_can('activate_plugins')) {
            return;
        }
        if (!function_exists('is_plugin_active')) {
            require_once ABSPATH . 'wp-admin/includes/plugin.php';
        }
        if (!\is_plugin_active('stager/stager.php')) {
            return;
        }
        \deactivate_plugins('stager/stager.php', true);
        update_option('tempel_stager_standalone_replaced', time(), false);
        add_action('admin_notices', static function (): void {
            echo '<div class="notice notice-info is-dismissible"><p>' . esc_html__('De losse Stager-plugin is uitgeschakeld, omdat Website verhuizen nu in Tempel zit.', 'tempel-settings') . '</p></div>';
        });
    }

    public static function preflight_issues(): array
    {
        try {
            self::migration()->check(false);
            return array();
        } catch (\Throwable $error) {
            return array(sanitize_text_field($error->getMessage()));
        }
    }

    public function browse(): void
    {
        $this->guard_ajax();
        nocache_headers();
        try {
            $settings = wp_unslash($_POST['sftp'] ?? array());
            $path = wp_unslash($_POST['path'] ?? '');
            if (!is_array($settings) || count(array_filter($settings, 'is_string')) !== count($settings) || !is_string($path)) {
                throw new \RuntimeException(__('Ongeldige verbindingsgegevens.', 'tempel-settings'));
            }
            wp_send_json_success((new \Tempel_Stager_SFTP())->browse($settings, $path));
        } catch (\Throwable $error) {
            wp_send_json_error(array('message' => sanitize_text_field($error->getMessage())), 400);
        }
    }

    public function export(): void
    {
        $this->guard_action('tempel_stager_export');
        $migration = self::migration();
        try {
            $migration->check();
            $file = $migration->export();
            nocache_headers();
            header('Content-Type: application/zip');
            header('Content-Disposition: attachment; filename="tempel-sitepakket-' . gmdate('Y-m-d-His') . '.zip"');
            header('Content-Length: ' . filesize($file));
            readfile($file);
            $migration->cleanup();
            exit;
        } catch (\Throwable $error) {
            $migration->cleanup();
            $this->failure($error);
        }
    }

    /** Builds a private package while streaming progress to the current admin screen. */
    public function prepare_export(): void
    {
        $this->guard_action('tempel_stager_prepare_export');
        if (function_exists('set_time_limit')) {
            @set_time_limit(0);
        }
        nocache_headers();
        header('Content-Type: application/x-ndjson; charset=utf-8');
        header('X-Accel-Buffering: no');
        while (ob_get_level() > 0 && @ob_end_clean()) {
            // Progress must reach the browser instead of remaining in an output buffer.
        }
        $emit = static function (array $event): void {
            echo wp_json_encode($event) . "\n";
            flush();
        };
        $migration = self::migration();
        try {
            $emit(array('type' => 'progress', 'stage' => 'check', 'message' => __('Website controleren…', 'tempel-settings'), 'percent' => 5));
            $migration->check();
            $file = $migration->export($emit);
            $token = bin2hex(random_bytes(24));
            $filename = 'tempel-sitepakket-' . gmdate('Y-m-d-His') . '.zip';
            set_transient('tempel_stager_export_' . $token, array(
                'file' => $file,
                'user' => get_current_user_id(),
                'filename' => $filename,
            ), HOUR_IN_SECONDS);
            wp_schedule_single_event(time() + HOUR_IN_SECONDS, 'tempel_stager_cleanup_export', array($file));
            // wp_nonce_url() HTML-escapes ampersands. This URL is sent as JSON
            // and assigned through JavaScript, so build an unescaped URL here.
            $url = add_query_arg(array(
                'action' => 'tempel_stager_download_export',
                'token' => $token,
                '_wpnonce' => wp_create_nonce('tempel_stager_download_export_' . $token),
            ), admin_url('admin-post.php'));
            $emit(array(
                'type' => 'done',
                'message' => __('Het pakket is gemaakt en gecontroleerd.', 'tempel-settings'),
                'url' => $url,
                'size' => size_format((int) filesize($file)),
            ));
            // Do not call cleanup: the private package remains available for one hour.
            exit;
        } catch (\Throwable $error) {
            $migration->cleanup();
            $emit(array('type' => 'error', 'message' => sanitize_text_field($error->getMessage())));
            exit;
        }
    }

    public function download_export(): void
    {
        if (!current_user_can('manage_options')) {
            wp_die(esc_html__('Geen toegang.', 'tempel-settings'), '', array('response' => 403));
        }
        $token = sanitize_text_field(wp_unslash($_GET['token'] ?? ''));
        if (!preg_match('/^[a-f0-9]{48}$/D', $token)) {
            wp_die(esc_html__('Deze downloadlink is ongeldig.', 'tempel-settings'), '', array('response' => 400));
        }
        check_admin_referer('tempel_stager_download_export_' . $token);
        $export = get_transient('tempel_stager_export_' . $token);
        if (!is_array($export) || (int) ($export['user'] ?? 0) !== get_current_user_id()) {
            wp_die(esc_html__('Dit pakket is verlopen of hoort bij een andere beheerder.', 'tempel-settings'), '', array('response' => 410));
        }
        $file = (string) ($export['file'] ?? '');
        if (!$this->is_private_export($file) || !is_readable($file)) {
            delete_transient('tempel_stager_export_' . $token);
            wp_die(esc_html__('Het gemaakte pakket is niet meer beschikbaar.', 'tempel-settings'), '', array('response' => 410));
        }
        delete_transient('tempel_stager_export_' . $token);
        nocache_headers();
        header('Content-Type: application/zip');
        header('Content-Disposition: attachment; filename="' . sanitize_file_name((string) $export['filename']) . '"');
        header('Content-Length: ' . filesize($file));
        readfile($file);
        $this->cleanup_export($file);
        exit;
    }

    public function cleanup_export(string $file): void
    {
        if (!$this->is_private_export($file)) {
            return;
        }
        $directory = dirname($file);
        $iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($directory, \FilesystemIterator::SKIP_DOTS), \RecursiveIteratorIterator::CHILD_FIRST);
        foreach ($iterator as $item) {
            $item->isDir() && !$item->isLink() ? @rmdir($item->getPathname()) : @unlink($item->getPathname());
        }
        @rmdir($directory);
    }

    private function is_private_export(string $file): bool
    {
        $temp = realpath(sys_get_temp_dir());
        $directory = realpath(dirname($file));
        return $temp !== false
            && $directory !== false
            && str_starts_with(wp_normalize_path($directory), trailingslashit(wp_normalize_path($temp)) . 'stager-')
            && basename($file) === 'package.zip';
    }

    public function dropbox_packages(): void
    {
        $this->guard_ajax();
        try {
            wp_send_json_success(array('packages' => (new Dropbox_Backup())->portable_packages()));
        } catch (\Throwable $error) {
            wp_send_json_error(array('message' => sanitize_text_field($error->getMessage())), 400);
        }
    }

    public function import(): void
    {
        $this->guard_action('tempel_stager_import');
        $this->assert_confirmation();
        if (function_exists('set_time_limit')) { @set_time_limit(0); }
        $migration = self::migration();
        $downloaded = '';
        try {
            $migration->check();
            $dropbox_path = sanitize_text_field(wp_unslash($_POST['dropbox_path'] ?? ''));
            if ($dropbox_path !== '') {
                $downloaded = (new Dropbox_Backup())->download_portable_package($dropbox_path);
                $package = $downloaded;
            } else {
                $file = $_FILES['package'] ?? array();
                if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK || !is_uploaded_file($file['tmp_name'] ?? '')) {
                    throw new \RuntimeException(__('Kies een verhuispakket of een Tempel-sitepakket uit Dropbox.', 'tempel-settings'));
                }
                $package = $file['tmp_name'];
            }
            $migration->import($package);
            $migration->cleanup();
            if ($downloaded !== '') { @unlink($downloaded); }
            wp_die(
                '<h1>' . esc_html__('De website is verhuisd', 'tempel-settings') . '</h1><p>' . esc_html__('Log opnieuw in met het account uit het verhuispakket. Sla daarna de permalinks opnieuw op en controleer de website.', 'tempel-settings') . '</p><p><a href="' . esc_url(wp_login_url()) . '">' . esc_html__('Naar inloggen', 'tempel-settings') . '</a></p>',
                esc_html__('Tempel Verhuizen', 'tempel-settings'),
                array('response' => 200)
            );
        } catch (\Throwable $error) {
            $migration->cleanup();
            if ($downloaded !== '') { @unlink($downloaded); }
            $this->failure($error);
        }
    }

    public function deploy(): void
    {
        $this->guard_action('tempel_stager_deploy');
        $this->assert_confirmation();
        if (function_exists('set_time_limit')) {
            @set_time_limit(900);
        }
        $stream = ($_POST['stager_stream'] ?? '') === '1';
        $emit = null;
        if ($stream) {
            nocache_headers();
            header('Content-Type: application/x-ndjson; charset=utf-8');
            header('X-Accel-Buffering: no');
            while (ob_get_level() > 0 && @ob_end_clean()) {
                // Remove buffering so progress reaches the browser.
            }
            $emit = static function (array $event): void {
                echo wp_json_encode($event) . "\n";
                flush();
            };
            $emit(array('type' => 'progress', 'stage' => 'connect', 'message' => __('Verhuizing voorbereiden…', 'tempel-settings'), 'percent' => null));
        }

        $migration = self::migration();
        try {
            $migration->check();
            $settings = wp_unslash($_POST['sftp'] ?? array());
            if (!is_array($settings) || count(array_filter($settings, 'is_string')) !== count($settings)) {
                throw new \RuntimeException(__('Ongeldige verbindingsgegevens.', 'tempel-settings'));
            }
            $url = (new \Tempel_Stager_SFTP($emit))->deploy($settings, $migration);
            $migration->cleanup();
            if ($stream) {
                $emit(array('type' => 'done', 'url' => $url, 'message' => __('Overdracht voltooid. Controleer nu de online website.', 'tempel-settings')));
                exit;
            }
            wp_safe_redirect($url);
            exit;
        } catch (\Throwable $error) {
            $migration->cleanup();
            if ($stream && is_callable($emit)) {
                $emit(array('type' => 'error', 'message' => sanitize_text_field($error->getMessage())));
                exit;
            }
            $this->failure($error);
        }
    }

    private static function migration(): \Tempel_Stager_Migration
    {
        require_once TEMPEL_SETTINGS_DIR . 'src/includes/stager/class-stager-values.php';
        require_once TEMPEL_SETTINGS_DIR . 'src/includes/stager/class-stager-migration.php';
        require_once TEMPEL_SETTINGS_DIR . 'src/includes/stager/class-stager-sftp.php';
        return new \Tempel_Stager_Migration();
    }

    private function guard_action(string $action): void
    {
        if (!current_user_can('manage_options')) {
            wp_die(esc_html__('Geen toegang.', 'tempel-settings'), '', array('response' => 403));
        }
        check_admin_referer($action);
    }

    private function guard_ajax(): void
    {
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => __('Geen toegang.', 'tempel-settings')), 403);
        }
        check_ajax_referer('tempel_stager_browse', 'nonce');
    }

    private function assert_confirmation(): void
    {
        if (empty($_POST['backup'])) {
            wp_die(esc_html__('Bevestig dat er een recente back-up beschikbaar is.', 'tempel-settings'), '', array('response' => 400, 'back_link' => true));
        }
    }

    private function failure(\Throwable $error): void
    {
        wp_die(esc_html($error->getMessage()), esc_html__('Verhuizing niet voltooid', 'tempel-settings'), array('back_link' => true, 'response' => 400));
    }
}
