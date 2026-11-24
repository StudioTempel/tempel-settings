<?php

if (PHP_SAPI !== 'cli') exit;
function templeton_check($condition, string $label): void {
    if (!$condition) throw new RuntimeException($label);
    echo "PASS: {$label}\n";
}

$root = dirname(__DIR__);
$guidelines = file_get_contents($root . '/src/includes/settings/templeton-guidelines.php');
$view = file_get_contents($root . '/src/views/templeton-settings.php');
$admin = file_get_contents($root . '/src/admin.php');
$assistant = file_get_contents($root . '/src/includes/settings/ai-assistant.php');
$navigation = file_get_contents($root . '/src/views/partials/settings-navigation.php');

templeton_check(strpos($admin, "'tempel-templeton-settings'") !== false && strpos($navigation, "'tempel-templeton-settings'") !== false, 'Templeton has an administrator settings page');
templeton_check(strpos($admin, "'tempel_templeton_guidelines'") !== false && strpos($admin, "Templeton_Guidelines::class, 'sanitize'") !== false, 'Templeton policy uses the Settings API');
templeton_check(strpos($guidelines, "'writing_style'") !== false && strpos($guidelines, "'facts_claims'") !== false && strpos($guidelines, "'quality_check'") !== false, 'Policy separates style, factual and quality requirements');
templeton_check(strpos($guidelines, 'sanitize_textarea_field') !== false && strpos($guidelines, '16000') !== false, 'Administrator policy is sanitized and length limited');
templeton_check(strpos($guidelines, 'page_workflow') !== false && strpos($guidelines, 'zoekintentie') !== false && strpos($guidelines, 'SEO-titel') !== false, 'Templeton contains the approved SEO page workflow');
templeton_check(strpos($assistant, 'Templeton_Guidelines::instructions') !== false && substr_count($assistant, 'templeton_instructions(') >= 3, 'Every AI generation path receives Templeton policy');
templeton_check(strpos($view, 'Templeton Engine') !== false && strpos($view, 'Algemeen kader en bedrijfs-DNA') !== false, 'Templeton page explains policy versus company DNA');
templeton_check(strpos($view, 'Wat Templeton automatisch meeneemt') !== false && strpos($view, 'Content_Inventory::acf_fields') !== false && strpos($view, 'AI_DNA::fields') !== false, 'Templeton page exposes its live source and context coverage');
templeton_check(strpos($view, "settings_fields('tempel_templeton_guidelines')") !== false, 'Templeton fields support central live saving');
