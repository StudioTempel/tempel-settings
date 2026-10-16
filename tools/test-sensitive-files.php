<?php
require dirname(__DIR__) . '/src/includes/settings/sensitive-files.php';
function check($ok, $label) {
    if (!$ok) { throw new RuntimeException($label); }
    echo "PASS: $label\n";
}
$original = "# BEGIN WordPress\nRewriteEngine On\n# END WordPress\n# custom rule\n";
$enabled = Tempel\Sensitive_Files::managed_content($original, true);
check(strpos($enabled, $original) !== false, 'Existing rules are unchanged');
check(substr_count($enabled, '# BEGIN TEMPEL SECURITY') === 1, 'Exactly one managed block');
$again = Tempel\Sensitive_Files::managed_content($enabled, true);
check(substr_count($again, '# BEGIN TEMPEL SECURITY') === 1, 'Repeated application does not duplicate rules');
$disabled = Tempel\Sensitive_Files::managed_content($enabled, false);
check(strpos($disabled, $original) !== false && strpos($disabled, 'Require all denied') === false, 'Removal preserves existing rules');
foreach (array('# BEGIN TEMPEL SECURITY', '# END TEMPEL SECURITY\n# BEGIN TEMPEL SECURITY') as $broken) {
    $caught = false;
    try { Tempel\Sensitive_Files::managed_content($broken, true); } catch (RuntimeException $error) { $caught = true; }
    check($caught, 'Malformed markers are rejected');
}
preg_match('/<FilesMatch "(.*?)">/', $enabled, $match);
$pattern = '~' . str_replace('~', '\\~', $match[1]) . '~';
foreach (array('.env', '.env.production', 'wp-config.php', 'wp-config.php.bak', 'composer.json', 'phpinfo.php', 'docker-compose.yaml') as $file) {
    check(preg_match($pattern, $file) === 1, "$file blocked");
}
foreach (array('index.php', 'wp-login.php', 'admin-ajax.php', 'catalog.yaml', 'product.yml', 'style.css') as $file) {
    check(preg_match($pattern, $file) === 0, "$file unaffected");
}

$fixture = sys_get_temp_dir() . '/tempel-security-' . bin2hex(random_bytes(6)) . '/';
mkdir($fixture . 'wp-admin/includes', 0700, true);
file_put_contents($fixture . 'wp-admin/includes/file.php', '<?php');
file_put_contents($fixture . '.htaccess', $original);
define('ABSPATH', $fixture);
function add_action(...$args) {}
function is_multisite() { return false; }
function get_home_path() { return $GLOBALS['fixture']; }
function update_option($key, $value, ...$args) { $GLOBALS['options'][$key] = $value; }
function home_url($path) { return 'https://example.test' . $path; }
function wp_login_url() { return 'https://example.test/wp-login.php'; }
function wp_remote_get(...$args) { return array('code' => array_shift($GLOBALS['codes']) ?? 200); }
function is_wp_error($response) { return false; }
function wp_remote_retrieve_response_code($response) { return $response['code']; }
$module = new Tempel\Sensitive_Files();
try {
    $codes = array(200, 200, 200, 200);
    $module->apply(true);
    check(strpos(file_get_contents($fixture . '.htaccess'), '# BEGIN TEMPEL SECURITY') !== false, 'Rules written to actual fixture');
    check($options['tempel_security_rules_backup']['content'] === $original, 'Original stored in database backup');
    $module->apply(false);
    check(strpos(file_get_contents($fixture . '.htaccess'), '# BEGIN TEMPEL SECURITY') === false, 'Recovery removes only managed block');
    $before = file_get_contents($fixture . '.htaccess');
    $codes = array(200, 200, 500, 200);
    $module->apply(true);
    check(file_get_contents($fixture . '.htaccess') === $before, 'Failed health check restores previous file');
} finally {
    unlink($fixture . '.htaccess');
    unlink($fixture . 'wp-admin/includes/file.php');
    rmdir($fixture . 'wp-admin/includes');
    rmdir($fixture . 'wp-admin');
    rmdir($fixture);
}
