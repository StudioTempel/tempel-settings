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
$options['tmpl_settings'] = array('security_lock' => 'on', 'form_entry_retention_enabled' => '', 'form_entry_retention_days' => '365');
$retention_settings = $admin->sanitize_general_settings(array('form_entry_retention_enabled' => 'on', 'form_entry_retention_days' => '14'));
check($retention_settings['form_entry_retention_enabled'] === 'on', 'Retention can be enabled');
check($retention_settings['form_entry_retention_days'] === '14', 'Retention days saved');
check($admin->sanitize_general_settings(array('form_entry_retention_enabled' => '', 'form_entry_retention_days' => '0'))['form_entry_retention_days'] === '1', 'Retention days have safe minimum');
check($admin->sanitize_general_settings(array('form_entry_retention_enabled' => 'on', 'form_entry_retention_days' => '9999'))['form_entry_retention_days'] === '3650', 'Retention days have safe maximum');
$options['tmpl_settings'] = array('security_lock' => 'on', 'form_entry_retention_enabled' => 'on', 'form_entry_retention_days' => '14');
check($admin->sanitize_general_settings(array('performance_enabled' => 'on'))['form_entry_retention_enabled'] === 'on', 'Other settings page preserves retention');
$options['tmpl_settings'] = array(
    'enable_branding' => 'on',
    'security_lock' => 'on',
    'email_login_verification' => 'on',
    'disable_comments' => 'on',
    'duplicate_content' => 'on',
    'form_entry_retention_enabled' => 'on',
    'form_entry_retention_days' => '14',
);
$gravity_forms_save = $admin->sanitize_general_settings(array(
    'form_entry_retention_enabled' => 'on',
    'form_entry_retention_days' => '30',
    'gf_antispam_enabled' => 'on',
    'gf_antispam_min_seconds' => '3',
));
check($gravity_forms_save['enable_branding'] === 'on', 'Gravity Forms save preserves branding');
check($gravity_forms_save['security_lock'] === 'on', 'Gravity Forms save preserves security lock');
check($gravity_forms_save['email_login_verification'] === 'on', 'Gravity Forms save preserves email verification');
check($gravity_forms_save['disable_comments'] === 'on', 'Gravity Forms save preserves general feature settings');
check($gravity_forms_save['duplicate_content'] === 'on', 'Gravity Forms save preserves duplicate content setting');
check($gravity_forms_save['performance_enabled'] ?? '' === '', 'Gravity Forms save does not enable unrelated performance settings');
$options['tmpl_settings']['performance_enabled'] = 'on';
$options['tmpl_settings']['performance_disable_emojis'] = 'on';
$performance_save = $admin->sanitize_general_settings(array(
    'performance_enabled' => 'on',
    'performance_frontend_memory_limit' => '128',
    'performance_admin_memory_limit' => '256',
));
check($performance_save['security_lock'] === 'on', 'Performance save preserves general settings');
check($performance_save['form_entry_retention_enabled'] === 'on', 'Performance save preserves Gravity Forms settings');
$general_save = $admin->sanitize_general_settings(array('security_lock' => 'on', 'enable_branding' => 'on'));
check($general_save['form_entry_retention_enabled'] === 'on', 'General save preserves Gravity Forms settings');
check($general_save['performance_enabled'] === 'on', 'General save preserves performance settings');
$options['tmpl_settings']['email_login_verification'] = '';
check($admin->sanitize_general_settings(array('email_login_verification' => 'on'))['email_login_verification'] === 'on', 'Email login verification can be enabled');
check($admin->sanitize_general_settings(array('email_login_verification' => ''))['email_login_verification'] === '', 'Email login verification can be disabled');

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

$repair = new ReflectionMethod($plugin, 'repair_general_settings_after_gravity_forms_save');
$repair->setAccessible(true);
$options = array('tmpl_settings' => array(
    'enable_branding' => '',
    'security_lock' => '',
    'email_login_verification' => '',
    'disable_comments' => '',
    'disable_default_pt' => '',
    'hide_dashboard_widgets' => '',
    'skip_bundled_themes' => '',
    'svg_support' => '',
    'taxonomy_order' => '',
    'duplicate_content' => '',
    'user_switching' => '',
    'form_entry_retention_days' => '14',
));
$repair->invoke($plugin);
foreach (array('enable_branding', 'security_lock', 'disable_comments', 'disable_default_pt', 'hide_dashboard_widgets', 'skip_bundled_themes', 'svg_support', 'taxonomy_order', 'duplicate_content', 'user_switching') as $key) {
    check($options['tmpl_settings'][$key] === 'on', "One-time repair restores $key");
}
check($options['tmpl_settings']['email_login_verification'] === '', 'One-time repair leaves email verification disabled');
$options['tmpl_settings']['enable_branding'] = '';
$repair->invoke($plugin);
check($options['tmpl_settings']['enable_branding'] === '', 'One-time repair does not override later manual changes');

$options = array('tmpl_settings' => array(
    'enable_branding' => 'on',
    'security_lock' => '',
    'form_entry_retention_days' => '14',
));
$repair->invoke($plugin);
check($options['tmpl_settings']['security_lock'] === '', 'Repair leaves intentionally mixed general settings unchanged');

function is_multisite() { return (bool) ($GLOBALS['multisite'] ?? false); }
function get_site_option($key, $default = false) { return $GLOBALS['site_options'][$key] ?? $default; }
function update_site_option($key, $value) { $GLOBALS['site_options'][$key] = $value; }
function get_file_data($file, $headers) {
    preg_match('/^\s*\*?\s*Version:\s*(.+)$/mi', file_get_contents($file), $match);
    return array('Version' => trim($match[1] ?? ''));
}
function deactivate_plugins($plugin, $silent = false, $network = null) {
    $GLOBALS['deactivations'][] = array($plugin, $network);
    if ($network) {
        unset($GLOBALS['site_options']['active_sitewide_plugins'][$plugin]);
    } else {
        $GLOBALS['options']['active_plugins'] = array_values(array_diff($GLOBALS['options']['active_plugins'] ?? array(), array($plugin)));
    }
}
define('WP_PLUGIN_DIR', sys_get_temp_dir() . '/tempel-security-test-plugins');
@mkdir(WP_PLUGIN_DIR . '/wpmudev-updates', 0777, true);
$wpmu = new ReflectionMethod($plugin, 'deactivate_vulnerable_wpmudev_dashboard');
$wpmu->setAccessible(true);
function write_wpmu_version($version) {
    file_put_contents(WP_PLUGIN_DIR . '/wpmudev-updates/update-notifications.php', "<?php\n/*\nVersion: $version\n*/\n");
}
$options = array('active_plugins' => array('wpmudev-updates/update-notifications.php'));
$site_options = array();
$deactivations = array();
write_wpmu_version('5.0.1');
$wpmu->invoke($plugin);
check($deactivations === array(array('wpmudev-updates/update-notifications.php', false)), 'Vulnerable active WPMU DEV Dashboard deactivated');
check($options['tempel_settings_wpmudev_deactivated_version'] === '5.0.1', 'Handled WPMU version stored');
$wpmu->invoke($plugin);
check(count($deactivations) === 1, 'Same vulnerable version handled once');
$options = array('active_plugins' => array('wpmudev-updates/update-notifications.php'));
$deactivations = array();
write_wpmu_version('5.0.2');
$wpmu->invoke($plugin);
check($deactivations === array(), 'Patched WPMU DEV Dashboard remains active');
$options = array('active_plugins' => array());
$deactivations = array();
write_wpmu_version('5.0.0');
$wpmu->invoke($plugin);
check($deactivations === array(), 'Inactive WPMU DEV Dashboard unchanged');
check(defined('DISALLOW_FILE_EDIT') && DISALLOW_FILE_EDIT, 'Security lock disables file editor');
