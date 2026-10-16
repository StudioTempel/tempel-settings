<?php
// Standalone regression checks: php tools/test-dropbox-backup.php
if (PHP_SAPI !== 'cli') {
    exit;
}

$fixture = sys_get_temp_dir() . '/tempel-dropbox-' . bin2hex(random_bytes(6)) . '/';
mkdir($fixture . 'wp-content/cache', 0700, true);
mkdir($fixture . 'wp-content/plugins/example', 0700, true);
file_put_contents($fixture . 'index.php', '<?php');
file_put_contents($fixture . 'wp-content/cache/skip.txt', 'cache');
file_put_contents($fixture . 'wp-content/plugins/example/plugin.php', '<?php');
define('ABSPATH', $fixture);
define('DAY_IN_SECONDS', 86400);
$GLOBALS['tempel_options'] = array();
$GLOBALS['tempel_hooks'] = array();

function add_action($hook, $callback) { $GLOBALS['tempel_hooks']['action'][$hook][] = $callback; }
function add_filter($hook, $callback) { $GLOBALS['tempel_hooks']['filter'][$hook][] = $callback; }
function add_option($key, $value, $deprecated = '', $autoload = null) { if (!isset($GLOBALS['tempel_options'][$key])) { $GLOBALS['tempel_options'][$key] = $value; } }
function __($text, $domain = '') { return $text; }
function get_option($key, $default = false) { return $GLOBALS['tempel_options'][$key] ?? $default; }
function wp_parse_args($args, $defaults) { return array_merge($defaults, $args); }
function wp_normalize_path($path) { return str_replace('\\', '/', $path); }
function wp_json_encode($value, $flags = 0) { return json_encode($value, $flags); }
function home_url($path = '') { return 'https://example.test' . $path; }
function get_bloginfo($key) { return $key === 'version' ? '7.1' : ''; }
function is_multisite() { return false; }
function apply_filters($hook, $value, ...$args) { return $value; }
function untrailingslashit($value) { return rtrim($value, '/\\'); }

require dirname(__DIR__) . '/src/includes/settings/dropbox-backup.php';

function check($condition, $label) {
    if (!$condition) {
        throw new RuntimeException($label);
    }
    echo "PASS: {$label}\n";
}

$module = new Tempel\Dropbox_Backup();
$defaults = Tempel\Dropbox_Backup::defaults();

check($defaults['enabled'] === '', 'Backups are disabled by default');
check($defaults['frequency'] === 'weekly', 'Weekly is the default frequency');
check($defaults['time'] === '03:30', 'Nighttime is the default backup time');
check($defaults['retention'] === 4, 'Four backups are retained by default');
check($defaults['notification_email'] === 'info@studiotempel.nl', 'Failure email has the Studio Tempel default recipient');
check($defaults['notify_success'] === '', 'Success emails are opt-in');
check(isset($GLOBALS['tempel_hooks']['action']['wp_loaded']), 'Schedule synchronization runs after init');
check(isset($GLOBALS['tempel_hooks']['action'][Tempel\Dropbox_Backup::CRON_HOOK]), 'Recurring backup hook is registered');
check(isset($GLOBALS['tempel_hooks']['action']['admin_post_tempel_dropbox_callback']), 'OAuth callback is registered');
check(isset($GLOBALS['tempel_hooks']['action']['wp_ajax_tempel_dropbox_list_folders']), 'Secure folder browser endpoint is registered');
$admin_source = file_get_contents(dirname(__DIR__) . '/src/admin.php');
check(strpos($admin_source, "'tempel-settings_page_tempel-backup-settings'") !== false, 'Backup page loads the shared Tempel settings styles');
check(strpos($admin_source, "filemtime(TEMPEL_SETTINGS_ASSET_DIR . 'css/settings-overrides.css')") !== false, 'Settings CSS uses cache-busting version');
check(!Tempel\Dropbox_Backup::is_connected(), 'Missing refresh token is not connected');

$GLOBALS['tempel_options'][Tempel\Dropbox_Backup::CREDENTIALS_OPTION] = array('refresh_token' => 'test');
check(Tempel\Dropbox_Backup::is_connected(), 'Refresh token marks Dropbox as connected');

$schedules = $module->cron_schedules(array());
check(($schedules['tempel_monthly']['interval'] ?? 0) === 30 * 86400, 'Monthly interval is thirty days');

$dump = $fixture . 'database.sql';
$archive = $fixture . 'backup.zip';
file_put_contents($dump, '-- database');
$method = new ReflectionMethod($module, 'create_archive');
$method->setAccessible(true);
$method->invoke($module, $archive, $dump);
$zip = new ZipArchive();
check($zip->open($archive) === true, 'Backup archive can be opened');
check($zip->locateName('database.sql') !== false, 'Database export is included');
check($zip->locateName('site/index.php') !== false, 'WordPress files are included');
check($zip->locateName('site/wp-content/plugins/example/plugin.php') !== false, 'Plugin files are included');
check($zip->locateName('site/wp-content/cache/skip.txt') === false, 'Cache files are excluded');
$zip->close();

unlink($archive);
unlink($dump);
unlink($fixture . 'index.php');
unlink($fixture . 'wp-content/cache/skip.txt');
unlink($fixture . 'wp-content/plugins/example/plugin.php');
rmdir($fixture . 'wp-content/cache');
rmdir($fixture . 'wp-content/plugins/example');
rmdir($fixture . 'wp-content/plugins');
rmdir($fixture . 'wp-content');
rmdir($fixture);
