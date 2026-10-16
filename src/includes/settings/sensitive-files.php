<?php

namespace Tempel;

class Sensitive_Files
{
    private const BEGIN = '# BEGIN TEMPEL SECURITY';
    private const END = '# END TEMPEL SECURITY';

    public function __construct()
    {
        add_action('update_option_tmpl_settings', array($this, 'settings_changed'), 10, 2);
        add_action('admin_post_tempel_remove_security_rules', array($this, 'remove_rules'));
    }

    public static function managed_content(string $content, bool $enabled): string
    {
        $begins = substr_count($content, self::BEGIN);
        $ends = substr_count($content, self::END);
        if ($begins !== $ends || $begins > 1) {
            throw new \RuntimeException('Onvolledige of dubbele Tempel-markers; bestand niet gewijzigd.');
        }
        if ($begins) {
            $start = strpos($content, self::BEGIN);
            $end = strpos($content, self::END);
            if ($end < $start) {
                throw new \RuntimeException('Ongeldige volgorde van Tempel-markers.');
            }
            $length = $end + strlen(self::END) - $start;
            $content = substr_replace($content, '', $start, $length);
        }
        if (!$enabled) {
            return $content;
        }
        $rules = "\n" . self::BEGIN . "\n"
            . '<FilesMatch "(?i)^(?:\.env(?:\..*)?|wp-config\.php(?:\..*|~)?|composer\.(?:json|lock)|phpinfo\.php|info\.php|docker-compose\.ya?ml|compose\.ya?ml|\.gitlab-ci\.yml)$">' . "\n"
            . "Require all denied\n</FilesMatch>\n" . self::END . "\n";
        // Prepend without touching WordPress or other managed blocks.
        return $rules . $content;
    }

    private function path(): string
    {
        require_once ABSPATH . 'wp-admin/includes/file.php';
        return get_home_path() . '.htaccess';
    }

    public function settings_changed($old, $new): void
    {
        $before = is_array($old) && ($old['sensitive_files_enabled'] ?? '') === 'on';
        $after = is_array($new) && ($new['sensitive_files_enabled'] ?? '') === 'on';
        if ($before !== $after) {
            $this->apply($after);
        }
    }

    private function health(): array
    {
        $results = array();
        foreach (array(home_url('/'), wp_login_url()) as $url) {
            $response = wp_remote_get($url, array('timeout' => 10, 'redirection' => 3, 'headers' => array('Cache-Control' => 'no-cache')));
            $results[] = is_wp_error($response) ? 0 : wp_remote_retrieve_response_code($response);
        }
        return $results;
    }

    public function apply(bool $enabled): void
    {
        if (is_multisite()) {
            update_option('tempel_security_rules_status', 'Niet toegepast: deze eerste versie ondersteunt geen gedeelde multisite-.htaccess.');
            return;
        }
        $path = $this->path();
        // Do not create a new server configuration file on unsupported hosting.
        if (!is_file($path) || !is_readable($path) || !is_writable($path)) {
            update_option('tempel_security_rules_status', 'Niet toegepast: bestaande .htaccess ontbreekt of is niet leesbaar/schrijfbaar.');
            return;
        }
        $baseline = $enabled ? $this->health() : array();
        $handle = fopen($path, 'r+');
        if (!$handle || !flock($handle, LOCK_EX)) {
            if ($handle) { fclose($handle); }
            update_option('tempel_security_rules_status', 'Niet toegepast: .htaccess kon niet worden vergrendeld.');
            return;
        }
        $original = stream_get_contents($handle);
        try {
            if ($original === false) { throw new \RuntimeException('Lezen van .htaccess mislukt.'); }
            $replacement = self::managed_content($original, $enabled);
            // Backup lives in the database, never as a publicly downloadable file.
            update_option('tempel_security_rules_backup', array('content' => $original, 'date' => gmdate('c'), 'path' => $path), false);
            rewind($handle);
            if (fwrite($handle, $replacement) !== strlen($replacement) || !ftruncate($handle, strlen($replacement)) || !fflush($handle)) {
                throw new \RuntimeException('Schrijven van .htaccess mislukt.');
            }
            if ($enabled) {
                $after = $this->health();
                foreach ($baseline as $index => $code) {
                    if ($code >= 200 && $code < 400 && ($after[$index] === 0 || $after[$index] >= 400)) {
                        throw new \RuntimeException('Homepage/login-controle verslechterde; wijziging teruggedraaid.');
                    }
                }
            }
            $status = $enabled ? 'Regels geplaatst. Controleer op de server of blokkades daadwerkelijk 403 geven. Formulieren en WooCommerce nog functioneel testen.' : 'Het eigen Tempel Security-blok is verwijderd; overige regels zijn behouden.';
            if ($enabled && count(array_filter($baseline, static function ($code) { return $code >= 200 && $code < 400; })) < 2) {
                $status .= ' Niet alle automatische loopbackcontroles waren beschikbaar.';
            }
            update_option('tempel_security_rules_status', $status);
        } catch (\Throwable $error) {
            if (is_string($original)) {
                rewind($handle);
                fwrite($handle, $original);
                ftruncate($handle, strlen($original));
                fflush($handle);
            }
            update_option('tempel_security_rules_status', $error->getMessage());
        } finally {
            flock($handle, LOCK_UN);
            fclose($handle);
        }
    }

    public function remove_rules(): void
    {
        if (!current_user_can('manage_options')) { wp_die('Geen toestemming.'); }
        check_admin_referer('tempel_remove_security_rules');
        $settings = get_option('tmpl_settings', array());
        $settings = is_array($settings) ? $settings : array();
        $settings['sensitive_files_enabled'] = '';
        // Explicit removal also works if the switch was already off after an error.
        remove_action('update_option_tmpl_settings', array($this, 'settings_changed'), 10);
        update_option('tmpl_settings', $settings);
        $this->apply(false);
        wp_safe_redirect(admin_url('admin.php?page=tempel-settings'));
        exit;
    }
}
