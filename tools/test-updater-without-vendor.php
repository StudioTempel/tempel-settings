<?php
// Standalone regression check: php tools/test-updater-without-vendor.php
if (PHP_SAPI !== 'cli') {
    exit;
}

function plugin_dir_path($file) {
    return dirname($file) . '/';
}

$root = sys_get_temp_dir() . '/tempel-updater-test-' . bin2hex(random_bytes(6));
$includes = $root . '/includes';
mkdir($includes, 0777, true);
copy(dirname(__DIR__) . '/includes/updater.php', $includes . '/updater.php');

require $includes . '/updater.php';
new Tempel\Updater();

echo "PASS: Missing updater library does not cause a fatal error\n";
