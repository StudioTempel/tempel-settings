<?php
// Standalone regression checks: php tools/test-notifications.php
if (PHP_SAPI !== 'cli') {
    exit;
}

function add_action(...$args) {}
function add_filter(...$args) {}
function __($text, $domain) { return $text; }
function wp_timezone() { return new DateTimeZone('Europe/Amsterdam'); }
function did_action($hook) { return $GLOBALS['completed_actions'][$hook] ?? 0; }
function register_post_type($post_type, $args) { $GLOBALS['registered_post_types'][$post_type] = $args; }
function acf_add_local_field_group($group) { $GLOBALS['acf_field_groups'][] = $group; }

require dirname(__DIR__) . '/src/includes/settings/notifications.php';

function check($condition, $label) {
    if (!$condition) {
        throw new RuntimeException($label);
    }
    echo "PASS: $label\n";
}

use Tempel\Notifications;

$completed_actions = array('init' => 1, 'acf/init' => 1);
$notification_module = new Notifications();
check(isset($registered_post_types['tempel_notification']), 'Post type registers when init already ran');
check(($acf_field_groups[0]['key'] ?? '') === 'group_tempel_notification', 'ACF fields register when ACF init already ran');
$dismissible_field = array_values(array_filter($acf_field_groups[0]['fields'], static function ($field) {
    return ($field['name'] ?? '') === 'tempel_notification_dismissible';
}))[0] ?? array();
check(($acf_field_groups[0]['style'] ?? '') === 'seamless', 'ACF field group has no metabox styling');
$field_names_in_order = array_column($acf_field_groups[0]['fields'], 'name');
check(array_search('tempel_notification_dismissible', $field_names_in_order, true) === array_search('tempel_notification_backdrop', $field_names_in_order, true) + 1, 'Close-button option follows backdrop option');
$backdrop_field = array_values(array_filter($acf_field_groups[0]['fields'], static function ($field) {
    return ($field['name'] ?? '') === 'tempel_notification_backdrop';
}))[0] ?? array();
check(($backdrop_field['conditional_logic'][0][0]['value'] ?? '') === 'center', 'Backdrop option is visible only for center popups');
$compact_field = array_values(array_filter($acf_field_groups[0]['fields'], static function ($field) {
    return ($field['name'] ?? '') === 'tempel_notification_compact';
}))[0] ?? array();
check(($compact_field['conditional_logic'][0][0]['operator'] ?? '') === '!=', 'Compact option is available only for bars');
$field_names = array_column($acf_field_groups[0]['fields'], 'name');
check(!in_array('tempel_notification_class', $field_names, true), 'Custom CSS class field is absent');
check(!in_array('tempel_notification_audience', $field_names, true), 'Audience field is absent');
check(!in_array('tempel_notification_priority', $field_names, true), 'Priority field is absent');
$toggle_fields = array_values(array_filter($acf_field_groups[0]['fields'], static function ($field) {
    return ($field['type'] ?? '') === 'true_false';
}));
check(count($toggle_fields) === 5 && count(array_filter($toggle_fields, static function ($field) {
    return ($field['ui_on_text'] ?? null) === '' && ($field['ui_off_text'] ?? null) === '';
})) === 5, 'All ACF toggles omit Yes and No labels');
$tab_labels = array_column(array_values(array_filter($acf_field_groups[0]['fields'], static function ($field) {
    return ($field['type'] ?? '') === 'tab';
})), 'label');
check($tab_labels === array('Inhoud', 'Instellingen'), 'Fields use only content and settings tabs');
check(in_array('field_tempel_notification_reset', array_column($acf_field_groups[0]['fields'], 'key'), true), 'Frequency reset control is registered');
$date_fields = array_values(array_filter($acf_field_groups[0]['fields'], static function ($field) {
    return in_array(($field['name'] ?? ''), array('tempel_notification_start', 'tempel_notification_end'), true);
}));
check(count($date_fields) === 2 && ($date_fields[0]['wrapper']['width'] ?? '') === '50' && ($date_fields[1]['wrapper']['width'] ?? '') === '50', 'Start and end dates share one row');
check(($date_fields[0]['return_format'] ?? '') === 'Y-m-d H:i' && ($date_fields[1]['return_format'] ?? '') === 'Y-m-d H:i', 'Date fields omit seconds');
check(!Notifications::is_dismissible('center', false), 'Center popup may omit its close button');
check(!Notifications::is_dismissible('top', false), 'Top bar may omit its close button');
check(Notifications::is_dismissible('bottom', true), 'Bottom bar may show its close button');

check(Notifications::parse_patterns(" /diensten/*\n\n/contact ") === array('/diensten/*', '/contact'), 'URL patterns are trimmed');
check(Notifications::matches_patterns('/diensten/websites', 'https://example.test/diensten/websites', array('/diensten/*')), 'Wildcard path matches');
check(Notifications::matches_patterns('/contact', 'https://example.test/contact', array('https://example.test/contact')), 'Full URL matches');
check(!Notifications::matches_patterns('/over-ons', 'https://example.test/over-ons', array('/contact')), 'Different path does not match');

$now = (new DateTimeImmutable('2026-09-15 12:00:00', wp_timezone()))->getTimestamp();
check(Notifications::is_within_schedule('', '', $now), 'Empty schedule is active');
check(Notifications::is_within_schedule('2026-09-15 11:00:00', '2026-09-15 13:00:00', $now), 'Current schedule is active');
check(!Notifications::is_within_schedule('2026-09-15 13:00:00', '', $now), 'Future schedule is inactive');
check(!Notifications::is_within_schedule('', '2026-09-15 11:00:00', $now), 'Expired schedule is inactive');
check(!Notifications::is_within_schedule('geen datum', '', $now), 'Invalid schedule fails closed');
