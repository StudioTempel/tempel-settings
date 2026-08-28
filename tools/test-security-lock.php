<?php
// Standalone regression checks: php tools/test-security-lock.php
if (PHP_SAPI !== 'cli') {
    exit;
}
define('TEMPEL_SETTINGS_DIR', dirname(__DIR__) . '/');
function add_filter(...$args) {}
function add_action(...$args) {}
function __($text, $domain) { return $text; }
function absint($value) { return abs((int) $value); }
function get_current_user_id() { return 1; }
function get_userdata($id) { return (object) array('roles' => $id === 1 ? array('administrator') : array('editor')); }
function get_user_meta($id, $key, $single) { return $id === 1 ? array('administrator' => true) : array('editor' => true); }
function get_option($key, $default = false) { return $GLOBALS['options'][$key] ?? $default; }
function is_wp_error($value) { return $value instanceof WP_Error; }
class WP_Error {
    public $errors = array();
    public function __construct($code = '', $message = '', $data = null) { if ($code) { $this->add($code, $message); } }
    public function add($code, $message) { $this->errors[$code] = $message; }
}
$wpdb = new class { public function get_blog_prefix() { return 'wp_'; } };
require TEMPEL_SETTINGS_DIR . 'src/includes/settings/security-lock.php';
require TEMPEL_SETTINGS_DIR . 'src/admin.php';
function check($condition, $label) {
    if (!$condition) { throw new RuntimeException($label); }
    echo "PASS: $label\n";
}
$lock = new Tempel\Security_Lock();
foreach (array('install_plugins', 'upload_plugins') as $cap) {
    check($lock->restrict_plugin_installation(array($cap), $cap) === array('do_not_allow'), "$cap denied");
}
foreach (array('update_plugins', 'activate_plugins', 'manage_options', 'create_users') as $cap) {
    check($lock->restrict_plugin_installation(array($cap), $cap) === array($cap), "$cap unchanged");
}
$roles = array('administrator' => array(), 'editor' => array());
$pagenow = 'user-new.php';
check(!isset($lock->filter_editable_roles($roles)['administrator']), 'Administrator hidden for new users');
$pagenow = 'user-edit.php';
$_REQUEST['user_id'] = 1;
check(isset($lock->filter_editable_roles($roles)['administrator']), 'Existing administrator role retained');
$_REQUEST['user_id'] = 2;
check(!isset($lock->filter_editable_roles($roles)['administrator']), 'Administrator hidden for editors');
foreach (array(array(false, 0, 'administrator', true), array(true, 2, 'administrator', true), array(true, 1, 'administrator', false), array(false, 0, 'editor', false)) as [$update, $id, $role, $blocked]) {
    $errors = new WP_Error();
    $lock->validate_user($errors, $update, (object) array('ID' => $id, 'role' => $role));
    check((bool) $errors->errors === $blocked, "Admin form: update=$update id=$id role=$role");
    $result = $lock->validate_rest_user((object) array(), array('id' => $id, 'roles' => array($role)));
    check(is_wp_error($result) === $blocked, "REST: id=$id role=$role");
}
$options['default_role'] = 'administrator';
check(is_wp_error($lock->validate_rest_user((object) array(), array())), 'REST default administrator denied');
check($lock->prevent_administrator_assignment(null, 2, 'wp_capabilities', array('administrator' => true), null) === false, 'Programmatic promotion denied');
check($lock->prevent_administrator_assignment(null, 1, 'wp_capabilities', array('administrator' => true), null) === null, 'Existing administrator metadata retained');
check($lock->prevent_administrator_assignment(null, 2, 'wp_capabilities', array('editor' => true), null) === null, 'Other roles allowed');
check($lock->prevent_administrator_assignment(null, 2, 'other_meta', array('administrator' => true), null) === null, 'Unrelated metadata unchanged');
$admin = (new ReflectionClass(Tempel\Admin::class))->newInstanceWithoutConstructor();
$options['tmpl_settings'] = array('security_lock' => 'on');
check($admin->sanitize_general_settings(array('security_lock' => ''))['security_lock'] === '', 'Last checked option can be disabled');
check($admin->sanitize_general_settings(array('security_lock' => 'on'))['security_lock'] === 'on', 'Lock saved');
check($admin->sanitize_general_settings(array('performance_enabled' => 'on'))['security_lock'] === 'on', 'Other settings page preserves lock');
check($admin->sanitize_general_settings(array('security_lock' => 'invalid'))['security_lock'] === '', 'Invalid checkbox value rejected');

// Test activation and the one-time upgrade without booting WordPress.
function add_option($key, $value) { if (!isset($GLOBALS['options'][$key])) { $GLOBALS['options'][$key] = $value; } }
function update_option($key, $value) { $GLOBALS['options'][$key] = $value; }
function plugin_basename($file) { return basename($file); }
function plugin_dir_path($file) { return dirname($file) . '/'; }
function plugin_dir_url($file) { return 'https://example.test/plugins/tempel-settings/'; }
function register_activation_hook(...$args) {}
define('ABSPATH', TEMPEL_SETTINGS_DIR);
require TEMPEL_SETTINGS_DIR . 'includes/activator.php';
require TEMPEL_SETTINGS_DIR . 'tempel.php';
$options = array();
Tempel\Activator::register_options();
check($options['tmpl_settings']['security_lock'] === 'on', 'New installation defaults to locked');
$plugin = (new ReflectionClass(Tempel\TempelSettings::class))->newInstanceWithoutConstructor();
$migration = new ReflectionMethod($plugin, 'apply_security_lock_default');
$migration->setAccessible(true);
$options = array('tmpl_settings' => array('security_lock' => '', 'enable_branding' => ''));
$migration->invoke($plugin);
check($options['tmpl_settings']['security_lock'] === 'on', 'Upgrade enables lock once');
check($options['tmpl_settings']['enable_branding'] === '', 'Upgrade preserves other settings');
$options['tmpl_settings']['security_lock'] = '';
$migration->invoke($plugin);
check($options['tmpl_settings']['security_lock'] === '', 'Manual disabling survives subsequent requests');
