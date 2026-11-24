<?php

if (PHP_SAPI !== 'cli') exit;
function inventory_check($condition, string $label): void {
    if (!$condition) throw new RuntimeException($label);
    echo "PASS: {$label}\n";
}

$root = dirname(__DIR__);
$inventory = file_get_contents($root . '/src/includes/settings/content-inventory.php');
$view = file_get_contents($root . '/src/views/content-inventory-settings.php');
$admin = file_get_contents($root . '/src/admin.php');
$assistant = file_get_contents($root . '/src/includes/settings/ai-assistant.php');
$navigation = file_get_contents($root . '/src/views/partials/settings-navigation.php');

inventory_check(strpos($admin, "'tempel-content-inventory'") !== false && strpos($navigation, "'tempel-content-inventory'") !== false, 'Content inventory is registered in Content navigation');
inventory_check(strpos($admin, "'tempel_content_inventory'") !== false && strpos($admin, "Content_Inventory::class, 'sanitize'") !== false, 'Inventory selections use the Settings API');
inventory_check(strpos($inventory, "get_post_types(array('public' => true)") !== false && strpos($inventory, 'wp_count_posts') !== false, 'Public content types and counts are inventoried');
inventory_check(strpos($inventory, 'acf_get_field_groups') !== false && strpos($inventory, 'sub_fields') !== false, 'ACF groups and nested fields are inventoried');
inventory_check(strpos($inventory, "'flexible_content'") !== false && strpos($inventory, "['layouts']") !== false && strpos($inventory, "['acf_fc_layout']") !== false, 'Flexible Content layouts and their nested fields are inventoried explicitly');
inventory_check(strpos($inventory, 'is_sensitive') !== false && strpos($inventory, 'password|passwd|secret|token') !== false, 'Sensitive field names remain excluded');
inventory_check(strpos($inventory, 'Yoast SEO') !== false && strpos($inventory, 'Rank Math') !== false && strpos($inventory, 'SEOPress') !== false, 'Common SEO providers are inventoried');
inventory_check(strpos($view, 'Contentinventaris') !== false && strpos($view, 'tempel-content-summary') !== false, 'Inventory page shows a clear source summary');
inventory_check(strpos($assistant, 'Content_Inventory::enabled_post_types') !== false && strpos($assistant, 'Content_Inventory::seo_text_for_post') !== false, 'DNA research obeys content inventory selections');
