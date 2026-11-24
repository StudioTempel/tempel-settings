<?php

if (PHP_SAPI !== 'cli') exit(1);

$root = dirname(__DIR__);
$main = file_get_contents($root . '/tempel.php');
if (!is_string($main) || !preg_match('/Version:\s*([0-9]+\.[0-9]+\.[0-9]+)/', $main, $matches)) {
    throw new RuntimeException('Kan pluginversie niet bepalen.');
}

$version = $matches[1];
$archive = $root . '/tempel-settings-' . $version . '-plugin.zip';
$plugins_directory = '/Users/dorusbos/Local Sites/dataspark/app/public/wp-content/plugins';

if (!is_file($archive)) {
    throw new RuntimeException('Maak eerst de releasezip: ' . basename($archive));
}
if (!is_dir($plugins_directory . '/tempel-settings')) {
    throw new RuntimeException('De Tempel-pluginmap van dataspark bestaat niet.');
}

$zip = new ZipArchive();
if ($zip->open($archive, ZipArchive::CHECKCONS) !== true) {
    throw new RuntimeException('De releasezip kan niet veilig worden geopend.');
}
if ($zip->locateName('tempel-settings/tempel.php') === false) {
    $zip->close();
    throw new RuntimeException('De releasezip heeft niet de verwachte pluginstructuur.');
}
if (!$zip->extractTo($plugins_directory)) {
    $zip->close();
    throw new RuntimeException('Bijwerken van dataspark is mislukt.');
}
$zip->close();

$installed = file_get_contents($plugins_directory . '/tempel-settings/tempel.php');
if (!is_string($installed) || strpos($installed, "TEMPEL_SETTINGS_VERSION', '" . $version . "'") === false) {
    throw new RuntimeException('De geïnstalleerde versie kon niet worden bevestigd.');
}

echo 'Dataspark bijgewerkt naar Tempel ' . $version . PHP_EOL;
