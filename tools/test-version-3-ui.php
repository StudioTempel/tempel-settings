<?php
// Standalone source-level regression checks for the 3.0 admin foundation.
if (PHP_SAPI !== 'cli') exit;

function check($condition, $label) {
    if (!$condition) throw new RuntimeException($label);
    echo "PASS: {$label}\n";
}

$root = dirname(__DIR__);
$plugin = file_get_contents($root . '/tempel.php');
$admin = file_get_contents($root . '/src/admin.php');
$backup = file_get_contents($root . '/src/includes/settings/dropbox-backup.php');
$navigation = file_get_contents($root . '/src/views/partials/settings-navigation.php');
$components = file_get_contents($root . '/dist/css/admin-components.css');

check(strpos($plugin, "TEMPEL_SETTINGS_VERSION', '3.0.0") !== false, 'Plugin runtime version is 3.0.0');
check(file_exists($root . '/src/views/overview-settings.php'), 'Overview page exists');
check(strpos(file_get_contents($root . '/src/views/overview-settings.php'), 'Veelgebruikte acties') === false, 'Overview omits the redundant quick actions card');
$widget_view = file_get_contents($root . '/src/views/widget-settings.php');
$settings_js = file_get_contents($root . '/dist/js/settings.js');
check(strpos($widget_view, 'tempel-widget-field--full') !== false, 'Conversion form selection uses a full-width field');
check(substr_count($widget_view, 'tempel-post-type-dependent') === 2, 'Post type detail fields share a conditional UI class');
check(strpos($settings_js, 'syncPostTypeConversionFields') !== false, 'Post type detail fields follow their toggle without a reload');
check(file_exists($root . '/dist/css/admin-tokens.css'), 'Design tokens exist');
check(strpos($components, '.tempel-ui-progress') !== false, 'Shared progress component exists');
check(strpos($components, 'body.wp-admin.tempel-settings-admin') !== false, 'Every Tempel screen receives the central dark canvas');
check(strpos($components, 'margin-left: auto') !== false && strpos($components, '> .checkbox__switch') !== false, 'Settings toggles align at the right edge');
check(strpos($components, '.tempel-mail-editor-field .settings__field__inner') !== false, 'Mail WYSIWYG uses the full-width field layout');
check(strpos($components, 'background: #fff !important') !== false, 'Mail WYSIWYG keeps the familiar white document surface');
check(strpos($admin, "add_filter('admin_body_class', array(\$this, 'add_admin_body_class'))") !== false, 'Admin registers the future-proof Tempel screen body class');
check(strpos($admin, "remove_submenu_page('tempel-settings'") === false, 'Detail pages remain registered for direct access');
check(strpos($admin, 'tempel-settings-submenu-visibility') !== false, 'Duplicate detail links are hidden visually');
check(strpos($navigation, "__('Gereedschap'") !== false, 'Navigation contains the four-destination structure');
check(strpos($navigation, "'tempel-info-settings'") === false, 'Info no longer appears as a separate navigation item');
check(strpos(file_get_contents($root . '/src/views/status-settings.php'), 'render_sections()') !== false, 'Status and log page contains the Info sections');
check(strpos($admin, 'tempel_dropbox_backup_status') !== false, 'Live backup status is localized for the admin');
check(strpos($backup, "wp_ajax_tempel_dropbox_backup_status") !== false, 'Live backup status endpoint is registered');
check(strpos($backup, 'SecretStore::encrypt') !== false, 'Dropbox credentials are encrypted at rest');
check(strpos($admin, "array('tmpl-settings-overrides')") !== false, 'Components load after legacy overrides');
