<?php
// Source-level regression checks for the compact classic-editor publish box.
if (PHP_SAPI !== 'cli') exit;

function compact_publish_check($condition, $label) {
    if (!$condition) throw new RuntimeException($label);
    echo "PASS: {$label}\n";
}

$root = dirname(__DIR__);
$plugin = file_get_contents($root . '/tempel.php');
$admin = file_get_contents($root . '/src/admin.php');
$settings = file_get_contents($root . '/src/settings.php');
$view = file_get_contents($root . '/src/views/general-settings.php');
$class = file_get_contents($root . '/src/includes/settings/compact-publish-box.php');
$script = file_get_contents($root . '/dist/js/compact-publish-box.js');
$style = file_get_contents($root . '/dist/css/compact-publish-box.css');

compact_publish_check(strpos($admin, "'compact_publish_box'") !== false, 'Setting is sanitized');
compact_publish_check(strpos($view, 'id="compact_publish_box"') !== false, 'Appearance toggle exists');
compact_publish_check(strpos($plugin, 'apply_compact_publish_box_default') !== false, 'Feature receives a one-time default');
compact_publish_check(strpos($settings, 'new Compact_Publish_Box()') !== false, 'Enabled feature is loaded');
compact_publish_check(strpos($class, "array('post.php', 'post-new.php')") !== false, 'Assets are limited to editor screens');
compact_publish_check(strpos($script, 'screen-reader-text tempel-publish-label') !== false, 'Hidden labels remain available to screen readers');
compact_publish_check(strpos($script, '#post-status-display') !== false && strpos($script, '#post-visibility-display') !== false && strpos($script, '#timestamp') !== false, 'Only the requested publish rows are targeted');
compact_publish_check(strpos($script, '.remove(') === false && strpos($script, 'removeChild') === false, 'Publish controls are not removed');
compact_publish_check(strpos($style, '.misc-pub-section::before') !== false, 'WordPress publish icons remain visible');
compact_publish_check(strpos($style, 'margin: 0 !important') !== false && strpos($style, 'text-align: left') !== false, 'All publish rows align to the left');
compact_publish_check(strpos($style, 'top: 50%') !== false && strpos($style, 'translateY(-50%)') !== false, 'Icons align vertically with their row content');
