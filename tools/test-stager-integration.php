<?php
// Standalone structural regression checks: php tools/test-stager-integration.php
if (PHP_SAPI !== 'cli') { exit; }

function check_stager($condition, $label) {
    if (!$condition) { throw new RuntimeException($label); }
    echo "PASS: {$label}\n";
}

$root = dirname(__DIR__);
$module = file_get_contents($root . '/src/includes/settings/stager.php');
$view = file_get_contents($root . '/src/views/stager-settings.php');
$admin = file_get_contents($root . '/src/admin.php');
$settings = file_get_contents($root . '/src/settings.php');
$sftp = file_get_contents($root . '/src/includes/stager/class-stager-sftp.php');
$migration = file_get_contents($root . '/src/includes/stager/class-stager-migration.php');

check_stager(strpos($module, "current_user_can('manage_options')") !== false, 'Migration actions require administrator capability');
check_stager(strpos($module, "check_admin_referer") !== false, 'Destructive actions require a nonce');
check_stager(strpos($module, "['backup']") !== false && strpos($module, "'VERHUIZEN'") === false, 'Import and deploy require backup acknowledgement without typed confirmation');
check_stager(strpos($view, '<dialog id="stager-folders"') !== false, 'Server folder browser opens in an accessible dialog');
check_stager(strpos($module, "deactivate_plugins('stager/stager.php'") !== false, 'Standalone Stager is replaced without a class conflict');
check_stager(strpos($view, 'tempel-stager-backup') === false && strpos($view, 'tempel-stager-hero') === false, 'Migration page omits duplicate introduction and backup status');
check_stager(strpos($view, 'tempel-stager-grid--manual') !== false, 'Manual migration steps use a vertical layout');
check_stager(strpos($view, '1 · Op de bronsite') === false && strpos($view, '2 · Op de doelsite') === false, 'Manual migration cards omit redundant source and destination labels');
check_stager(strpos($view, 'PHP-uploadlimiet') !== false && strpos($view, 'wp_max_upload_size()') !== false, 'Browser upload limit is explained separately from package capacity');
check_stager(strpos($view, 'stager-dropbox-browse') !== false && strpos($module, 'download_portable_package') !== false, 'Portable packages can be selected directly from Dropbox');
check_stager(strpos($admin, 'tempel-stager-settings') !== false, 'Migration page is registered in Tempel navigation');
check_stager(strpos($settings, 'new Stager()') !== false, 'Migration controller is loaded');
check_stager(strpos($sftp, 'Tempel_Stager_SFTP') !== false, 'SFTP engine uses a conflict-free class name');
check_stager(strpos($migration, 'MAX_BYTES = 21474836480') !== false, 'Twenty GiB extraction limit remains enforced');
check_stager(strpos($migration, "'format' => 2") !== false && strpos($migration, "'database.sql'") !== false, 'Site packages contain portable migration data and a recovery SQL dump');
check_stager(strpos($migration, "'site/' . \$relative") !== false, 'Site packages include the complete WordPress root');
check_stager(strpos($migration, 'SET FOREIGN_KEY_CHECKS=0') !== false && strpos($migration, 'SELECT @@FOREIGN_KEY_CHECKS') !== false, 'Foreign keys are suspended and restored during atomic import');
check_stager(strpos($migration, "preg_match('/FOREIGN KEY") === false, 'Foreign-key tables remain supported in migration packages');
check_stager(strpos($migration, "preg_match('/DATA DIRECTORY|INDEX DIRECTORY|CONNECTION") !== false, 'External table storage remains blocked');
check_stager(file_exists($root . '/src/includes/stager/vendor/autoload.php'), 'Packaged SFTP dependency is present');
check_stager(file_exists($root . '/dist/js/stager-folder-browser.js'), 'Folder browser asset is present');
check_stager(file_exists($root . '/dist/js/stager-deploy-progress.js'), 'Progress asset is present');
check_stager(file_exists($root . '/dist/js/stager-export-progress.js') && strpos($view, 'stager-export-progress') !== false, 'Manual export is prepared with live progress before download');
check_stager(strpos($module, 'tempel_stager_download_export') !== false && strpos($module, 'set_transient') !== false, 'Prepared exports use a private expiring download link');
