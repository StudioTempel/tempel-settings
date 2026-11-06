<?php

if (PHP_SAPI !== 'cli') {
    exit(1);
}

$root = dirname(__DIR__);
$main = file_get_contents($root . '/tempel.php');
if (!is_string($main) || !preg_match('/Version:\s*([0-9]+\.[0-9]+\.[0-9]+)/', $main, $matches)) {
    throw new RuntimeException('Kan pluginversie niet bepalen.');
}

$version = $matches[1];
$destination = $root . '/tempel-settings-' . $version . '-plugin.zip';
$sources = array('CHANGELOG.md', 'readme.txt', 'tempel.php', 'dist', 'includes', 'languages', 'src', 'vendor');
$zip = new ZipArchive();

if ($zip->open($destination, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
    throw new RuntimeException('Kan releasezip niet maken.');
}

foreach ($sources as $source) {
    $path = $root . '/' . $source;
    if (!file_exists($path)) {
        $zip->close();
        throw new RuntimeException('Releasebron ontbreekt: ' . $source);
    }

    if (is_file($path)) {
        $zip->addFile($path, 'tempel-settings/' . $source);
        continue;
    }

    $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($path, FilesystemIterator::SKIP_DOTS));
    foreach ($iterator as $file) {
        if ($file->isLink() || !$file->isFile()) {
            continue;
        }
        $relative = ltrim(str_replace('\\', '/', substr($file->getPathname(), strlen($root))), '/');
        $zip->addFile($file->getPathname(), 'tempel-settings/' . $relative);
    }
}

if (!$zip->close()) {
    throw new RuntimeException('Kan releasezip niet afronden.');
}

$verify = new ZipArchive();
if ($verify->open($destination, ZipArchive::CHECKCONS) !== true) {
    throw new RuntimeException('Releasezip is beschadigd.');
}

$required = array(
    'tempel-settings/tempel.php',
    'tempel-settings/vendor/autoload.php',
    'tempel-settings/vendor/enshrined/svg-sanitize/src/Sanitizer.php',
    'tempel-settings/vendor/yahnis-elsts/plugin-update-checker/load-v5p7.php',
    'tempel-settings/src/includes/stager/vendor/autoload.php',
);
foreach ($required as $file) {
    if ($verify->locateName($file) === false) {
        $verify->close();
        throw new RuntimeException('Runtimebestand ontbreekt in release: ' . $file);
    }
}
$verify->close();

echo $destination . PHP_EOL;
