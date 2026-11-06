<?php
final class Tempel_Stager_Migration {
    private $work;
    private $lock;
    private $staged = [];
    private $journal = [];
    private $preserve_self;
    private const MAX_BYTES = 21474836480;

    public function __construct($preserve_self = true) { $this->preserve_self = $preserve_self; }

    public function check($start = true) {
        if (PHP_VERSION_ID < 80100) { throw new RuntimeException('Stager vereist PHP 8.1 of hoger.'); }
        if (is_multisite()) { throw new RuntimeException('Deze versie ondersteunt geen WordPress Multisite.'); }
        if (!class_exists('ZipArchive')) { throw new RuntimeException('Schakel de PHP-extensie ZipArchive in bij je hosting.'); }
        if (defined('DISALLOW_FILE_MODS') && DISALLOW_FILE_MODS) { throw new RuntimeException('Deze installatie blokkeert wijzigingen aan plugins en bestanden.'); }
        if (realpath(WP_PLUGIN_DIR) !== realpath(WP_CONTENT_DIR . '/plugins')) { throw new RuntimeException('Een aangepaste pluginmap wordt nog niet ondersteund.'); }
        $uploads = wp_get_upload_dir();
        if (untrailingslashit(wp_normalize_path($uploads['basedir'])) !== untrailingslashit(wp_normalize_path(WP_CONTENT_DIR . '/uploads'))) { throw new RuntimeException('Een aangepaste uploadsmap wordt nog niet ondersteund.'); }
        if ($start) {
            $temp = realpath(sys_get_temp_dir());
            if (!$temp || !is_writable($temp)) { throw new RuntimeException('De tijdelijke PHP-map is niet schrijfbaar.'); }
            foreach ([ABSPATH, WP_CONTENT_DIR, $_SERVER['DOCUMENT_ROOT'] ?? ABSPATH] as $public) {
                $public = realpath($public);
                if ($public && ($temp === $public || str_starts_with($temp . '/', rtrim($public, '/') . '/'))) {
                    throw new RuntimeException('Configureer een tijdelijke PHP-map buiten de openbare webmap.');
                }
            }
            $this->lock = fopen($temp . '/stager-' . hash('sha256', ABSPATH) . '.lock', 'c');
            if (!$this->lock || !flock($this->lock, LOCK_EX | LOCK_NB)) { throw new RuntimeException('Er loopt al een Stager-verhuizing.'); }
            $this->work = $temp . '/stager-' . bin2hex(random_bytes(16));
            if (!mkdir($this->work, 0700)) { throw new RuntimeException('Kan geen privéwerkmap maken.'); }
        }
    }

    private function query($sql) {
        global $wpdb;
        if ($wpdb->query($sql) === false) { throw new RuntimeException('Databasebewerking mislukt. Controleer databaserechten, compatibiliteit en vrije schijfruimte.'); }
    }

    private function identifier($name) {
        if (!preg_match('/^[a-zA-Z0-9_]+$/D', $name) || strlen($name) > 64) { throw new RuntimeException('Niet-ondersteunde tabelnaam.'); }
        return '`' . $name . '`';
    }

    private function tables() {
        global $wpdb;
        $tables = $wpdb->get_results($wpdb->prepare('SHOW FULL TABLES LIKE %s', $wpdb->esc_like($wpdb->prefix) . '%'), ARRAY_N);
        if ($wpdb->last_error || !$tables) { throw new RuntimeException('Kan de databasetabellen niet lezen.'); }
        foreach ($tables as $table) {
            if ($table[1] !== 'BASE TABLE') { throw new RuntimeException('Databaseviews worden nog niet ondersteund.'); }
        }
        return array_column($tables, 0);
    }

    public function export($progress = null) {
        global $wpdb, $wp_version;
        $emit = is_callable($progress) ? $progress : static function () {};
        $emit(['type' => 'progress', 'stage' => 'database', 'message' => 'Database exporteren…', 'percent' => 10]);
        $zip = new ZipArchive();
        $path = $this->work . '/package.zip';
        if ($zip->open($path, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) { throw new RuntimeException('Kan pakket niet maken.'); }
        $manifest = ['format' => 2, 'wordpress' => $wp_version, 'prefix' => $wpdb->prefix, 'home' => home_url(), 'siteurl' => site_url(), 'content_url' => content_url(), 'root' => ABSPATH, 'content_dir' => WP_CONTENT_DIR, 'tables' => [], 'files' => []];
        $sql_path = $this->work . '/database.sql';
        $sql = fopen($sql_path, 'wb');
        if (!$sql) { throw new RuntimeException('Kan SQL-herstelbestand niet schrijven.'); }
        fwrite($sql, "-- Tempel site package\nSET FOREIGN_KEY_CHECKS=0;\nSET SQL_MODE='NO_AUTO_VALUE_ON_ZERO';\n\n");
        $tables = $this->tables();
        $table_count = max(1, count($tables));
        foreach ($tables as $index => $table) {
            $schema = $wpdb->get_row('SHOW CREATE TABLE ' . $this->identifier($table), ARRAY_N);
            if (!$schema || $wpdb->last_error) { throw new RuntimeException('Kan tabelstructuur niet lezen.'); }
            if (preg_match('/DATA DIRECTORY|INDEX DIRECTORY|CONNECTION\s*=/i', $schema[1])) { throw new RuntimeException('Tabellen met externe opslag worden nog niet ondersteund.'); }
            fwrite($sql, 'DROP TABLE IF EXISTS ' . $this->identifier($table) . ";\n" . $schema[1] . ";\n\n");
            $name = 'database/' . $index . '.jsonl';
            $dump = $this->work . '/table-' . $index;
            $handle = fopen($dump, 'wb');
            if (!$handle) { throw new RuntimeException('Kan databasedump niet schrijven.'); }
            $offset = 0;
            do {
                $rows = $wpdb->get_results('SELECT * FROM ' . $this->identifier($table) . ' LIMIT 250 OFFSET ' . $offset, ARRAY_A);
                if ($wpdb->last_error) { throw new RuntimeException('Kan tabelgegevens niet lezen.'); }
                foreach ($rows as $row) {
                    $sql_columns = array_map(fn($column) => $this->sql_identifier((string) $column), array_keys($row));
                    $sql_values = array_map([$this, 'sql_value'], array_values($row));
                    fwrite($sql, 'INSERT INTO ' . $this->identifier($table) . ' (' . implode(',', $sql_columns) . ') VALUES (' . implode(',', $sql_values) . ");\n");
                    foreach ($row as &$value) { $value = $value === null ? null : base64_encode($value); } unset($value);
                    $line = json_encode($row, JSON_THROW_ON_ERROR) . "\n";
                    if (fwrite($handle, $line) !== strlen($line)) { throw new RuntimeException('Onvoldoende ruimte voor de database.'); }
                }
                $offset += count($rows);
            } while (count($rows) === 250);
            fclose($handle);
            if (!$zip->addFile($dump, $name)) { throw new RuntimeException('Kan database niet inpakken.'); }
            $manifest['tables'][] = ['name' => $table, 'schema' => $schema[1], 'file' => $name, 'sha256' => hash_file('sha256', $dump)];
            $emit(['type' => 'progress', 'stage' => 'database', 'message' => 'Database exporteren: ' . ($index + 1) . ' van ' . $table_count . ' tabellen…', 'percent' => 10 + (int) floor((($index + 1) / $table_count) * 30)]);
        }
        fwrite($sql, "SET FOREIGN_KEY_CHECKS=1;\n");
        fclose($sql);
        $zip->addFile($sql_path, 'database.sql');
        $zip->addFromString('tempel-backup.json', json_encode(['format' => 2, 'site_url' => home_url('/'), 'created_at' => gmdate('c'), 'wordpress' => $wp_version, 'php' => PHP_VERSION], JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES));
        $root = wp_normalize_path(ABSPATH);
        $emit(['type' => 'progress', 'stage' => 'files', 'message' => 'Websitebestanden verzamelen…', 'percent' => 45]);
        $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root, FilesystemIterator::SKIP_DOTS));
        $file_count = 0;
        foreach ($iterator as $file) {
                $path = wp_normalize_path($file->getPathname());
                $relative = ltrim(substr($path, strlen($root)), '/');
                if ($file->isLink() || !$file->isFile() || !$file->isReadable()) { continue; }
                if (Tempel_Stager_Values::excluded_file($relative)) { continue; }
                $name = 'site/' . $relative;
                if (!Tempel_Stager_Values::safe_path($name)) { throw new RuntimeException('Niet-ondersteund bestandspad: ' . $relative); }
                if (!$zip->addFile($file->getPathname(), $name)) { throw new RuntimeException('Kan bestand niet inpakken: ' . $relative); }
                $manifest['files'][$name] = hash_file('sha256', $file->getPathname());
                $file_count++;
                if ($file_count % 250 === 0) { $emit(['type' => 'progress', 'stage' => 'files', 'message' => number_format($file_count, 0, ',', '.') . ' bestanden toegevoegd…', 'percent' => null]); }
        }
        $parent_config = dirname(untrailingslashit($root)) . '/wp-config.php';
        if (!file_exists($root . 'wp-config.php') && is_readable($parent_config)) {
            $zip->addFile($parent_config, 'site/wp-config.php');
            $manifest['files']['site/wp-config.php'] = hash_file('sha256', $parent_config);
        }
        $zip->addFromString('manifest.json', json_encode($manifest, JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES));
        $emit(['type' => 'progress', 'stage' => 'finish', 'message' => 'ZIP-pakket afronden…', 'percent' => 85]);
        if (!$zip->close()) { throw new RuntimeException('Kan het ZIP-pakket niet afronden.'); }
        clearstatcache(true, $path);
        if (!is_file($path) || !is_readable($path) || filesize($path) < 1) { throw new RuntimeException('Het ZIP-pakket is niet goed opgeslagen. Controleer de vrije schijfruimte.'); }
        $emit(['type' => 'progress', 'stage' => 'verify', 'message' => 'Pakket controleren…', 'percent' => 92]);
        $verify = new ZipArchive();
        $opened = $verify->open($path, ZipArchive::RDONLY);
        if ($opened !== true) { throw new RuntimeException('Kan het gemaakte pakket niet controleren (ZIP-fout ' . (int) $opened . '). Controleer vrije schijfruimte en ZipArchive-ondersteuning.'); }
        $size = 0;
        for ($i = 0; $i < $verify->numFiles; $i++) { $size += $verify->statIndex($i)['size']; }
        $too_many = $verify->numFiles > 100000;
        $verify->close();
        if ($too_many || $size > self::MAX_BYTES) { throw new RuntimeException('Deze site is te groot voor dit pakket: maximaal 20 GiB uitgepakt en 100.000 bestanden.'); }
        $emit(['type' => 'progress', 'stage' => 'verify', 'message' => 'Pakket is gecontroleerd.', 'percent' => 100]);
        return $path;
    }

    private function sql_value($value) {
        global $wpdb;
        if ($value === null) { return 'NULL'; }
        $value = (string) $value;
        if (isset($wpdb->dbh) && $wpdb->dbh instanceof mysqli) { return "'" . mysqli_real_escape_string($wpdb->dbh, $value) . "'"; }
        return "'" . addslashes($value) . "'";
    }

    private function sql_identifier($name) {
        if ($name === '' || strlen($name) > 64 || preg_match('/[\x00-\x1f]/', $name)) { throw new RuntimeException('Niet-ondersteunde kolomnaam.'); }
        return '`' . str_replace('`', '``', $name) . '`';
    }

    private function unpack($path) {
        $zip = new ZipArchive();
        if ($zip->open($path) !== true) { throw new RuntimeException('Dit bestand is geen leesbaar ZIP-pakket.'); }
        try {
            $seen = []; $bytes = 0;
            if ($zip->numFiles > 100000) { throw new RuntimeException('Het pakket bevat te veel bestanden.'); }
            for ($i = 0; $i < $zip->numFiles; $i++) {
                $stat = $zip->statIndex($i); $name = $stat['name'];
                $bytes += $stat['size'];
                if ($bytes > self::MAX_BYTES || isset($seen[$name])) { throw new RuntimeException('Pakket te groot (maximaal 20 GiB uitgepakt) of dubbele bestandsnamen.'); }
                if ($name !== 'manifest.json' && $name !== 'database.sql' && $name !== 'tempel-backup.json' && !preg_match('~^database/[0-9]+\.jsonl$~D', $name) && !Tempel_Stager_Values::safe_path($name)) { throw new RuntimeException('Onveilig of onbekend pad in pakket.'); }
                $seen[$name] = true;
            }
            $free = @disk_free_space($this->work);
            $required = max(536870912, (int) ceil($bytes * 1.2));
            if ((is_int($free) || is_float($free)) && $free < $required) { throw new RuntimeException('Onvoldoende vrije tijdelijke schijfruimte om dit sitepakket veilig uit te pakken en te controleren.'); }
            $stat = $zip->statName('manifest.json');
            if (!$stat || $stat['size'] > 16777216) { throw new RuntimeException('Ontbrekend of te groot manifest.'); }
            $manifest = json_decode($zip->getFromName('manifest.json'), true, 64, JSON_THROW_ON_ERROR);
            if (!in_array(($manifest['format'] ?? null), [1, 2], true) || !is_array($manifest['tables'] ?? null) || !is_array($manifest['files'] ?? null)) { throw new RuntimeException('Dit is geen ondersteund Tempel-sitepakket.'); }
            foreach ($manifest['files'] as $name => $hash) {
                if (!Tempel_Stager_Values::safe_path($name)) { throw new RuntimeException('Ongeldig bestandspad in manifest.'); }
            }
            $expected = $manifest['files'];
            foreach ($manifest['tables'] as $table) {
                if (!preg_match('~^database/[0-9]+\.jsonl$~D', $table['file'] ?? '') || isset($expected[$table['file']])) { throw new RuntimeException('Ongeldige tabelverwijzing.'); }
                $expected[$table['file']] = $table['sha256'];
            }
            foreach ($expected as $name => $hash) {
                if (!isset($seen[$name]) || !is_string($hash) || !preg_match('/^[a-f0-9]{64}$/D', $hash)) { throw new RuntimeException('Het pakket is onvolledig.'); }
                $dest = $this->work . '/' . $name;
                if (!is_dir(dirname($dest)) && !mkdir(dirname($dest), 0700, true)) { throw new RuntimeException('Kan tijdelijke map niet maken.'); }
                $in = $zip->getStream($name); $out = fopen($dest, 'wb');
                if (!$in || !$out) { throw new RuntimeException('Kan pakket niet uitpakken.'); }
                $copied = stream_copy_to_stream($in, $out, self::MAX_BYTES + 1);
                fclose($in); fclose($out);
                if ($copied !== $zip->statName($name)['size'] || !hash_equals($hash, hash_file('sha256', $dest))) { throw new RuntimeException('Bestandscontrole mislukt; maak een nieuw pakket.'); }
            }
            return $manifest;
        } finally { $zip->close(); }
    }

    public function import($path) {
        global $wpdb, $wp_version;
        $manifest = $this->unpack($path);
        foreach (['home', 'siteurl', 'content_url', 'root', 'content_dir', 'prefix', 'wordpress'] as $key) {
            if (!is_string($manifest[$key] ?? null) || $manifest[$key] === '') { throw new RuntimeException('Onvolledig pakketmanifest.'); }
        }
        if (version_compare($wp_version, $manifest['wordpress'], '<')) { throw new RuntimeException('Werk de online WordPress-installatie eerst bij tot minimaal ' . $manifest['wordpress'] . '.'); }
        if (!preg_match('/^[a-zA-Z0-9_]+$/D', $manifest['prefix'])) { throw new RuntimeException('Ongeldige databaseprefix.'); }
        $map = [$manifest['content_url'] => content_url(), $manifest['siteurl'] => site_url(), $manifest['home'] => home_url(), $manifest['content_dir'] => WP_CONTENT_DIR, $manifest['root'] => ABSPATH];
        foreach ($map as $from => $to) { $map[str_replace('/', '\/', $from)] = str_replace('/', '\/', $to); }
        $token = 'stg' . bin2hex(random_bytes(6)) . '_';
        $destinations = [];
        $temporary_by_source = [];
        foreach ($this->tables() as $existing) {
            $schema = $wpdb->get_row('SHOW CREATE TABLE ' . $this->identifier($existing), ARRAY_N);
            if (!$schema || preg_match('/DATA DIRECTORY|INDEX DIRECTORY|CONNECTION\s*=/i', $schema[1])) { throw new RuntimeException('De doeldatabase bevat een tabel met externe opslag.'); }
        }
        foreach ($manifest['tables'] as $index => $table) {
            $source = $table['name'] ?? '';
            if (!is_string($source) || !str_starts_with($source, $manifest['prefix'])) { throw new RuntimeException('Tabel valt buiten de websiteprefix.'); }
            $suffix = substr($source, strlen($manifest['prefix']));
            $destination = $wpdb->prefix . $suffix;
            $this->identifier($source);
            $this->identifier($destination);
            if (isset($destinations[$destination]) || isset($temporary_by_source[$source])) { throw new RuntimeException('Dubbele tabel in pakket.'); }
            $temporary = $token . 'new_' . $index;
            $this->identifier($temporary);
            $destinations[$destination] = $temporary;
            $temporary_by_source[$source] = $temporary;
        }
        $foreign_key_checks = (int) $wpdb->get_var('SELECT @@FOREIGN_KEY_CHECKS');
        $this->query('SET FOREIGN_KEY_CHECKS=0');
        try {
            foreach ($manifest['tables'] as $index => $table) {
                $source = $table['name'];
                $suffix = substr($source, strlen($manifest['prefix']));
                $destination = $wpdb->prefix . $suffix;
                $temporary = $temporary_by_source[$source];
                $start = 'CREATE TABLE ' . $this->identifier($source);
                if (!is_string($table['schema'] ?? null) || !str_starts_with($table['schema'], $start . ' (') || preg_match('/DATA DIRECTORY|INDEX DIRECTORY|CONNECTION\s*=/i', $table['schema'])) { throw new RuntimeException('Niet-ondersteunde tabelstructuur.'); }
                $temporary_schema = 'CREATE TABLE ' . $this->identifier($temporary) . substr($table['schema'], strlen($start));
                foreach ($temporary_by_source as $referenced_source => $referenced_temporary) {
                    $temporary_schema = str_replace(
                        'REFERENCES ' . $this->identifier($referenced_source),
                        'REFERENCES ' . $this->identifier($referenced_temporary),
                        $temporary_schema
                    );
                }
                $constraint_index = 0;
                $temporary_schema = preg_replace_callback('/CONSTRAINT\s+`[^`]+`/i', function () use ($token, $index, &$constraint_index) {
                    return 'CONSTRAINT `' . $token . 'fk_' . $index . '_' . $constraint_index++ . '`';
                }, $temporary_schema);
                $this->query($temporary_schema);
                $this->staged[] = $temporary;
                $columns = $wpdb->get_results('SHOW COLUMNS FROM ' . $this->identifier($temporary), ARRAY_A);
                $generated = [];
                foreach ($columns as $column) { if (preg_match('/(?:VIRTUAL|STORED) GENERATED/', $column['Extra'])) { $generated[] = $column['Field']; } }
                $handle = fopen($this->work . '/' . $table['file'], 'rb');
                if (!$handle) { throw new RuntimeException('Kan tabelgegevens niet openen.'); }
                try {
                    while (($line = fgets($handle)) !== false) {
                        $row = json_decode($line, true, 64, JSON_THROW_ON_ERROR);
                        if (!is_array($row) || !$row) { throw new RuntimeException('Ongeldige tabelrij.'); }
                        foreach ($row as $key => &$value) {
                            if ($value !== null) {
                                $value = base64_decode($value, true);
                                if ($value === false) { throw new RuntimeException('Ongeldige tabelwaarde.'); }
                                $value = Tempel_Stager_Values::replace($value, $map);
                            }
                        } unset($value);
                        if ($suffix === 'options' && isset($row['option_name']) && str_starts_with($row['option_name'], $manifest['prefix'])) { $row['option_name'] = $wpdb->prefix . substr($row['option_name'], strlen($manifest['prefix'])); }
                        if ($suffix === 'usermeta' && isset($row['meta_key']) && str_starts_with($row['meta_key'], $manifest['prefix'])) { $row['meta_key'] = $wpdb->prefix . substr($row['meta_key'], strlen($manifest['prefix'])); }
                        foreach ($generated as $key) { unset($row[$key]); }
                        if ($wpdb->insert($temporary, $row) === false) { throw new RuntimeException('Import van tabelrij mislukt. Controleer databasecompatibiliteit.'); }
                    }
                    if (!feof($handle)) { throw new RuntimeException('Tabelbestand niet volledig gelezen.'); }
                } finally { fclose($handle); }
            }
            foreach (['options', 'users', 'usermeta', 'posts', 'postmeta', 'terms', 'term_taxonomy', 'term_relationships', 'termmeta', 'comments', 'commentmeta'] as $required) {
                if (!isset($destinations[$wpdb->prefix . $required])) { throw new RuntimeException('Een vereiste WordPress-tabel ontbreekt: ' . $required); }
            }
            $options = $destinations[$wpdb->options];
            foreach (['home' => home_url(), 'siteurl' => site_url(), 'upload_path' => '', 'upload_url_path' => '', 'rewrite_rules' => ''] as $key => $value) {
                if ($wpdb->update($options, ['option_value' => $value], ['option_name' => $key]) === false) { throw new RuntimeException('Kan doelinstellingen niet bewaren.'); }
            }
            $maintenance = ABSPATH . '.maintenance';
            $maintenance_handle = @fopen($maintenance, 'x');
            if (!$maintenance_handle) { throw new RuntimeException('Kan onderhoudsmodus niet starten; controleer schrijfrechten of een lopende update.'); }
            fwrite($maintenance_handle, '<?php $upgrading = ' . time() . ';');
            fclose($maintenance_handle);
            // Never activate another version of Stager over the currently executing importer.
            foreach ($manifest['files'] as $name => $hash) {
                if (str_starts_with($name, 'content/')) {
                    $relative = substr($name, strlen('content/'));
                    $target = WP_CONTENT_DIR . '/' . $relative;
                    $cursor = WP_CONTENT_DIR;
                } elseif (str_starts_with($name, 'site/')) {
                    $relative = substr($name, strlen('site/'));
                    if (strtolower($relative) === 'wp-config.php') { continue; }
                    $target = ABSPATH . $relative;
                    $cursor = untrailingslashit(ABSPATH);
                } else { throw new RuntimeException('Onbekend bestandspad in sitepakket.'); }
                $current_plugin = defined('TEMPEL_SETTINGS_DIR') ? ltrim(substr(wp_normalize_path(TEMPEL_SETTINGS_DIR), strlen(wp_normalize_path(ABSPATH))), '/') : '';
                if ($this->preserve_self && (($current_plugin !== '' && str_starts_with($relative, $current_plugin)) || str_starts_with($relative, 'plugins/stager/') || str_starts_with($relative, 'wp-content/plugins/stager/'))) { continue; }
                foreach (explode('/', $relative) as $part) {
                    $cursor .= '/' . $part;
                    if (is_link($cursor)) { throw new RuntimeException('Import via symbolische links is niet toegestaan.'); }
                }
                if (file_exists($target) && !is_file($target)) { throw new RuntimeException('Bestand conflicteert met een bestaande map.'); }
                if (!is_dir(dirname($target)) && !wp_mkdir_p(dirname($target))) { throw new RuntimeException('Kan doelmap niet maken.'); }
                $backup = null;
                if (is_file($target)) {
                    $backup = $this->work . '/restore-' . count($this->journal);
                    if (!copy($target, $backup)) { throw new RuntimeException('Kan bestaand bestand niet veiligstellen.'); }
                }
                $this->journal[] = [$target, $backup];
                if (!copy($this->work . '/' . $name, $target)) { throw new RuntimeException('Kan bestand niet plaatsen: ' . $relative); }
                chmod($target, 0644);
            }
            $rename = []; $old = [];
            foreach ($this->tables() as $index => $table) {
                // Prefixes such as "stg" must not accidentally include temporary tables.
                if (in_array($table, $this->staged, true)) { continue; }
                $backup = $token . 'old_' . $index;
                $rename[] = $this->identifier($table) . ' TO ' . $this->identifier($backup);
                $old[] = $backup;
            }
            foreach ($destinations as $destination => $temporary) { $rename[] = $this->identifier($temporary) . ' TO ' . $this->identifier($destination); }
            $this->query('RENAME TABLE ' . implode(', ', $rename));
            $this->staged = [];
            $this->journal = [];
            // Database switch succeeded. Cleanup must not trigger a partial file rollback.
            foreach ($old as $table) { $wpdb->query('DROP TABLE ' . $this->identifier($table)); }
            try { wp_cache_flush(); } catch (Throwable $ignored) { /* Import is committed; clear host caches manually. */ }
        } catch (Throwable $e) {
            $failed = false;
            foreach (array_reverse($this->journal) as [$target, $backup]) {
                if ($backup ? !copy($backup, $target) : (is_file($target) && !unlink($target))) { $failed = true; }
            }
            foreach ($this->staged as $table) { $wpdb->query('DROP TABLE IF EXISTS ' . $this->identifier($table)); }
            if ($failed) {
                $location = $this->work; $this->work = null;
                throw new RuntimeException('Herstel van bestanden onvolledig. Herstel je hostingbackup. Tijdelijke herstelbestanden staan privé in: ' . $location, 0, $e);
            }
            throw $e;
        } finally {
            $wpdb->query('SET FOREIGN_KEY_CHECKS=' . ($foreign_key_checks ? '1' : '0'));
            if (isset($maintenance_handle) && $maintenance_handle !== false && isset($maintenance) && is_file($maintenance)) { unlink($maintenance); }
        }
    }

    public function cleanup() {
        if ($this->work && is_dir($this->work)) {
            $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($this->work, FilesystemIterator::SKIP_DOTS), RecursiveIteratorIterator::CHILD_FIRST);
            foreach ($iterator as $file) { $file->isDir() && !$file->isLink() ? rmdir($file->getPathname()) : unlink($file->getPathname()); }
            rmdir($this->work); $this->work = null;
        }
        if (is_resource($this->lock)) { flock($this->lock, LOCK_UN); fclose($this->lock); }
    }
}
