<?php
/** SFTP uploads, followed by an authenticated HTTPS import on existing WordPress. */
final class Tempel_Stager_SFTP {
    private $progress;
    public function __construct($progress = null) { $this->progress = $progress; }
    private function report($stage, $message, $percent = null) {
        if (is_callable($this->progress)) { ($this->progress)(['type' => 'progress', 'stage' => $stage, 'message' => $message, 'percent' => $percent]); }
    }
    public const GUARD = "<?php http_response_code(404); exit; __halt_compiler();\n";

    public static function fingerprint($key) {
        $parts = explode(' ', $key);
        $decoded = isset($parts[1]) ? base64_decode($parts[1], true) : false;
        if ($decoded === false) { throw new RuntimeException('Kan de SSH-hostsleutel niet controleren.'); }
        return 'SHA256:' . rtrim(base64_encode(hash('sha256', $decoded, true)), '=');
    }

    private function connect(array $settings) {
        require_once __DIR__ . '/vendor/autoload.php';
        $host = trim($settings['host'] ?? '');
        $port = filter_var($settings['port'] ?? 22, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1, 'max_range' => 65535]]);
        if (!$host || preg_match('~[\s/\x00]~', $host) || !$port) { throw new RuntimeException('Vul een geldige SFTP-host en poort in.'); }
        $sftp = new \phpseclib3\Net\SFTP($host, $port, 30);
        try {
            $this->authenticate($sftp, $host, $port, $settings);
            return $sftp;
        } catch (Throwable $e) { $sftp->disconnect(); throw $e; }
    }

    private function authenticate($sftp, $host, $port, array $settings) {
        $option = 'stager_host_key_' . hash('sha256', strtolower($host) . ':' . $port);
        $actual = self::fingerprint($sftp->getServerPublicHostKey());
        $expected = get_option($option, false);
        if ($expected !== false && (!is_string($expected) || !hash_equals($expected, $actual))) {
            throw new RuntimeException('De serversleutel is gewijzigd. Verbinding gestopt vóór het inloggen. Laat je hostingbeheerder de wijziging controleren en de opgeslagen Stager-serversleutel herstellen.');
        }
        if (!$sftp->login($settings['username'] ?? '', $settings['password'] ?? '')) { throw new RuntimeException('SFTP-inloggen mislukt. Controleer gebruikersnaam en wachtwoord.'); }
        // Trust on first successful use. Never store passwords or overwrite a known key.
        if ($expected === false && !add_option($option, $actual, '', false)) {
            $saved = get_option($option, false);
            if (!is_string($saved) || !hash_equals($saved, $actual)) {
                throw new RuntimeException('Kan de serversleutel niet veilig onthouden. Controleer de lokale database en probeer opnieuw.');
            }
        }
    }

    public static function directory_path($path) {
        if (!is_string($path) || $path === '' || $path[0] !== '/' || preg_match('~[\x00-\x1f]|(^|/)\.{1,2}(/|$)~', $path)) { throw new RuntimeException('Kies een geldige absolute servermap.'); }
        return rtrim(preg_replace('~/+~', '/', $path), '/') ?: '/';
    }

    public function browse(array $settings, $path = '') {
        $sftp = $this->connect($settings);
        try { return $this->directory_listing($sftp, $path); }
        finally { $sftp->disconnect(); }
    }

    private function directory_listing($sftp, $path) {
        // Empty means the account's login directory, including chrooted shared hosting.
        if ($path === '') { $path = $sftp->pwd(); }
        $path = self::directory_path($path);
        if (!$sftp->chdir($path)) { throw new RuntimeException('Deze map bestaat niet of je hebt geen toegang.'); }
        $path = self::directory_path($sftp->pwd());
        $entries = $sftp->rawlist('.', false);
        if (!is_array($entries)) { throw new RuntimeException('Kan de mappen niet ophalen. Controleer je leesrechten.'); }
        $directories = [];
        foreach ($entries as $name => $entry) {
            $name = (string) $name;
            if ($name === '.' || $name === '..' || preg_match('~[/\x00-\x1f]~', $name) || ($entry['type'] ?? null) !== 2) { continue; }
            $directories[] = ['name' => $name, 'path' => rtrim($path, '/') . '/' . $name];
        }
        usort($directories, function ($a, $b) { return strnatcasecmp($a['name'], $b['name']); });
        return ['path' => $path, 'parent' => $path === '/' ? null : self::directory_path(dirname($path)), 'directories' => $directories, 'wordpress' => $sftp->is_file('wp-load.php') && $sftp->is_dir('wp-admin')];
    }

    public function deploy(array $settings, Tempel_Stager_Migration $migration) {
        $this->report('connect', 'Verbinden met je hosting…');
        $root = rtrim(self::directory_path(trim($settings['root'] ?? '')), '/');
        $url = rtrim(trim($settings['url'] ?? ''), '/');
        $parsed = wp_parse_url($url);
        if (($parsed['scheme'] ?? '') !== 'https' || empty($parsed['host']) || isset($parsed['user']) || isset($parsed['query']) || isset($parsed['fragment']) || !wp_http_validate_url($url)) { throw new RuntimeException('Gebruik een publiek bereikbaar HTTPS-adres zonder queryparameters.'); }
        $sftp = $this->connect($settings);
        $id = 'stager-transfer-' . bin2hex(random_bytes(16));
        $secret = bin2hex(random_bytes(32));
        $runner = $root . '/' . $id . '.php';
        $package = $root . '/' . $id . '-package.php';
        $status = $root . '/' . $id . '-status.php';
        $endpoint = $url . '/' . $id . '.php';
        $started = false; $definitive = false;
        try {
            if (!$sftp->is_file($root . '/wp-load.php') || !$sftp->is_dir($root . '/wp-admin')) { throw new RuntimeException('In deze servermap is geen WordPress-installatie gevonden.'); }
            // Version <= 0.1.2 omitted source files named backup*. Repair those
            // BEFORE wp-load.php: an active, incomplete plugin can prevent bootstrap.
            $this->report('repair', 'Ontbrekende programmabestanden controleren en herstellen…');
            $this->repair_omitted_source($sftp, $root, WP_CONTENT_DIR);
            $this->report('check', 'Online WordPress en HTTPS controleren…');
            if (!$sftp->put($runner, self::runner($id, $secret))) { throw new RuntimeException('Kan de tijdelijke verhuishulp niet uploaden.'); }
            $probe = self::request($endpoint, $secret, 'check');
            if (rtrim($probe['siteurl'] ?? '', '/') !== $url) { throw new RuntimeException('Het HTTPS-adres hoort niet bij deze WordPress-installatie. Vul het WordPress-adres van de doelinstallatie in.'); }
            global $wp_version;
            if (version_compare($probe['wordpress'] ?? '0', $wp_version, '<')) { throw new RuntimeException('Werk WordPress op de hosting eerst bij tot minimaal ' . $wp_version . '.'); }
            $this->report('pack', 'Database en bestanden inpakken…');
            $local = $migration->export();
            $wrapped = $local . '.php';
            $in = fopen($local, 'rb'); $out = fopen($wrapped, 'wb');
            if (!$in || !$out || fwrite($out, self::GUARD) !== strlen(self::GUARD)) { throw new RuntimeException('Kan upload niet voorbereiden.'); }
            $bytes = stream_copy_to_stream($in, $out); fclose($in); fclose($out);
            if ($bytes !== filesize($local)) { throw new RuntimeException('Onvoldoende schijfruimte voor upload.'); }
            $total = filesize($wrapped); $last_percent = -1;
            $this->report('upload', 'Verhuispakket uploaden…', 0);
            $on_upload = function ($sent) use ($total, &$last_percent) {
                $percent = min(100, (int) floor($sent * 100 / max(1, $total)));
                if ($percent !== $last_percent) { $last_percent = $percent; $this->report('upload', 'Verhuispakket uploaden…', $percent); }
            };
            if (!$sftp->put($package, $wrapped, \phpseclib3\Net\SFTP::SOURCE_LOCAL_FILE, -1, -1, $on_upload)) { throw new RuntimeException('SFTP-upload mislukt. De online site is nog niet vervangen.'); }
            $this->report('import', 'Bestanden en database op de hosting importeren. Dit kan enkele minuten duren…');
            $started = true;
            $result = self::request($endpoint, $secret, 'import', hash_file('sha256', $local));
            $definitive = true;
            if (empty($result['ok'])) { throw new RuntimeException($result['message'] ?? 'Import mislukt.'); }
            return $url;
        } catch (Throwable $e) {
            if ($started && !$definitive) {
                // Query a protected status file over SFTP: never repeat an uncertain import.
                $raw = $sftp->get($status);
                $state = is_string($raw) ? json_decode(substr($raw, strlen(self::GUARD)), true) : null;
                if (($state['state'] ?? '') === 'done') { $definitive = true; return $url; }
                if (($state['state'] ?? '') === 'error') { $definitive = true; throw new RuntimeException($state['message'] ?? 'Import mislukt.'); }
                throw new RuntimeException('Geen definitieve bevestiging ontvangen. Start niet opnieuw: controleer de online site en de serverstatus via SFTP in ' . $status . '. Tijdelijke bestanden beginnen met ' . $id . '. ' . $e->getMessage());
            }
            throw $e;
        } finally {
            if (!$started || $definitive) {
                foreach ([$runner, $package, $status] as $file) { $sftp->delete($file, false); }
            }
            $sftp->disconnect();
        }
    }

    private function repair_omitted_source($sftp, $root, $content) {
        $repaired = 0;
        foreach (['plugins', 'themes'] as $kind) {
            $directory = $content . '/' . $kind;
            if (!is_dir($directory) || is_link($directory)) { continue; }
            $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($directory, FilesystemIterator::SKIP_DOTS));
            foreach ($files as $file) {
                if ($file->isLink() || !$file->isFile()) { continue; }
                $relative = substr($file->getPathname(), strlen($content) + 1);
                if (!preg_match('~(^|/)(updraft|ai1wm-backups|backup[^/]*)(/|$)~i', $relative) || Tempel_Stager_Values::excluded_file($relative)) { continue; }
                if (!Tempel_Stager_Values::safe_path('content/' . $relative)) { throw new RuntimeException('Ongeldig herstelpad.'); }
                $target = $root . '/wp-content/' . $relative;
                $cursor = $root;
                foreach (explode('/', 'wp-content/' . $relative) as $part) {
                    $cursor .= '/' . $part;
                    if ($sftp->is_link($cursor)) { throw new RuntimeException('Herstel via symbolische links is niet toegestaan: ' . $relative); }
                }
                if ($sftp->is_file($target)) { continue; }
                // Never install a plugin/theme that is absent from the target.
                $parts = explode('/', $relative);
                if (count($parts) < 3 || !$sftp->is_dir($root . '/wp-content/' . $parts[0] . '/' . $parts[1])) { continue; }
                $parent = dirname($target);
                if (!$sftp->is_dir($parent) && !$sftp->mkdir($parent, 0755, true)) { throw new RuntimeException('Kan de ontbrekende programmamap niet herstellen: ' . $relative); }
                $temporary = $parent . '/stager-repair-' . bin2hex(random_bytes(12)) . '.php';
                try {
                    if (!$sftp->put($temporary, $file->getPathname(), \phpseclib3\Net\SFTP::SOURCE_LOCAL_FILE) || $sftp->filesize($temporary) !== $file->getSize()) {
                        throw new RuntimeException('Upload van ontbrekend programmabestand mislukt: ' . $relative);
                    }
                    if (!$sftp->rename($temporary, $target)) { throw new RuntimeException('Kan het herstelde bestand niet plaatsen: ' . $relative); }
                    $repaired++;
                } finally { $sftp->delete($temporary, false); }
            }
        }
        return $repaired;
    }

    private static function request($url, $secret, $action, $hash = '') {
        $response = wp_safe_remote_post($url, ['timeout' => $action === 'import' ? 300 : 45, 'redirection' => 0, 'sslverify' => true, 'headers' => ['X-Stager-Token' => $secret], 'body' => ['operation' => $action, 'sha256' => $hash], 'limit_response_size' => 65536]);
        if (is_wp_error($response)) { throw new RuntimeException('HTTPS-verbinding: ' . $response->get_error_message()); }
        $body = json_decode(wp_remote_retrieve_body($response), true);
        if (wp_remote_retrieve_response_code($response) !== 200 || !is_array($body)) { throw new RuntimeException('De verhuishulp gaf geen geldig antwoord. Controleer HTTPS, PHP en beveiligingsregels bij je hosting.'); }
        if (empty($body['ok'])) { throw new RuntimeException($body['message'] ?? 'Controle op de hosting mislukt.'); }
        return $body;
    }

    public static function runner($id, $secret) {
        $template = file_get_contents(__DIR__ . '/remote-runner.php.template');
        $classes = '';
        foreach (['class-stager-values.php', 'class-stager-migration.php'] as $file) {
            $classes .= str_replace(['Tempel_Stager_Values', 'Tempel_Stager_Migration'], ['Stager_Remote_Values', 'Stager_Remote_Migration'], substr(file_get_contents(__DIR__ . '/' . $file), 5));
        }
        return strtr($template, ['__TOKEN_HASH__' => var_export(hash('sha256', $secret), true), '__EXPIRES__' => (string) (time() + 3600), '__ID__' => var_export($id, true), '__GUARD__' => var_export(self::GUARD, true), '/*__CLASSES__*/' => $classes]);
    }
}
