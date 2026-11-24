<?php

namespace Tempel;

/** Discovers and controls the public content sources available to Tempel AI. */
final class Content_Inventory
{
    public const OPTION = 'tempel_content_inventory';

    public static function post_types(): array
    {
        $types = get_post_types(array('public' => true), 'objects');
        unset($types['attachment']);
        $output = array();
        foreach ($types as $slug => $type) {
            $counts = wp_count_posts($slug);
            $output[$slug] = array(
                'label' => (string) ($type->labels->name ?? $slug),
                'count' => (int) ($counts->publish ?? 0),
            );
        }
        return $output;
    }

    public static function acf_fields(): array
    {
        if (!function_exists('acf_get_field_groups') || !function_exists('acf_get_fields')) return array();
        $output = array();
        foreach ((array) acf_get_field_groups() as $group) {
            $group_label = sanitize_text_field((string) ($group['title'] ?? __('Veldgroep', 'tempel-settings')));
            self::collect_acf_fields((array) acf_get_fields($group), $output, $group_label);
        }
        return $output;
    }

    private static function collect_acf_fields(array $fields, array &$output, string $group, string $parent = ''): void
    {
        foreach ($fields as $field) {
            if (!is_array($field) || empty($field['key'])) continue;
            $label = sanitize_text_field((string) ($field['label'] ?? $field['name'] ?? $field['key']));
            $path = $parent !== '' ? $parent . ' › ' . $label : $label;
            $type = sanitize_key((string) ($field['type'] ?? 'text'));
            $output[(string) $field['key']] = array(
                'label' => $path,
                'name' => sanitize_key((string) ($field['name'] ?? '')),
                'group' => $group,
                'type' => $type,
                'supported' => self::is_textual_type($type) && !self::is_sensitive($path . ' ' . ($field['name'] ?? '')),
                'sensitive' => self::is_sensitive($path . ' ' . ($field['name'] ?? '')),
            );
            if (!empty($field['sub_fields']) && is_array($field['sub_fields'])) {
                self::collect_acf_fields($field['sub_fields'], $output, $group, $path);
            }
            if ($type === 'flexible_content' && !empty($field['layouts']) && is_array($field['layouts'])) {
                foreach ($field['layouts'] as $layout) {
                    if (!is_array($layout) || empty($layout['sub_fields'])) continue;
                    $layout_label = sanitize_text_field((string) ($layout['label'] ?? $layout['name'] ?? __('Onderdeel', 'tempel-settings')));
                    self::collect_acf_fields((array) $layout['sub_fields'], $output, $group, $path . ' › ' . $layout_label);
                }
            }
        }
        return;
    }

    public static function seo_sources(): array
    {
        return array(
            'yoast' => array(
                'label' => 'Yoast SEO',
                'detected' => defined('WPSEO_VERSION') || class_exists('WPSEO_Options'),
                'fields' => array('_yoast_wpseo_title', '_yoast_wpseo_metadesc', '_yoast_wpseo_focuskw'),
            ),
            'rank_math' => array(
                'label' => 'Rank Math',
                'detected' => defined('RANK_MATH_VERSION') || class_exists('RankMath'),
                'fields' => array('rank_math_title', 'rank_math_description', 'rank_math_focus_keyword'),
            ),
            'seopress' => array(
                'label' => 'SEOPress',
                'detected' => defined('SEOPRESS_VERSION') || defined('SEOPRESS_PRO_VERSION'),
                'fields' => array('_seopress_titles_title', '_seopress_titles_desc', '_seopress_analysis_target_kw'),
            ),
        );
    }

    public static function sanitize($input): array
    {
        $input = is_array($input) ? $input : array();
        return array(
            'post_types' => self::sanitize_selection((array) ($input['post_types'] ?? array()), array_keys(self::post_types())),
            'acf_fields' => self::sanitize_selection((array) ($input['acf_fields'] ?? array()), array_keys(self::acf_fields())),
            'seo' => self::sanitize_selection((array) ($input['seo'] ?? array()), array_keys(self::seo_sources())),
        );
    }

    private static function sanitize_selection(array $values, array $allowed): array
    {
        $selected = array_keys(array_filter($values, static fn($value): bool => $value === 'on' || $value === '1' || $value === 1 || $value === true));
        return array_values(array_intersect(array_map('sanitize_key', $selected), $allowed));
    }

    public static function selected(string $section, string $key, bool $available = true): bool
    {
        if (!$available) return false;
        $settings = get_option(self::OPTION, null);
        if (!is_array($settings) || !array_key_exists($section, $settings)) return true;
        return in_array($key, (array) $settings[$section], true);
    }

    public static function enabled_post_types(array $available): array
    {
        return array_values(array_filter($available, static fn($slug): bool => self::selected('post_types', (string) $slug)));
    }

    public static function acf_text_for_post(int $post_id): string
    {
        if (!function_exists('get_field_objects')) return '';
        $objects = get_field_objects($post_id, false, true);
        if (!is_array($objects)) return '';
        $parts = array();
        foreach ($objects as $field) self::collect_acf_value((array) $field, $field['value'] ?? null, $parts);
        return implode("\n", array_values(array_unique($parts)));
    }

    private static function collect_acf_value(array $field, $value, array &$parts, int $depth = 0): void
    {
        if ($depth > 8 || count($parts) >= 120) return;
        $key = (string) ($field['key'] ?? '');
        $name = (string) ($field['name'] ?? '');
        $label = (string) ($field['label'] ?? $name);
        if (self::is_sensitive($key . ' ' . $name . ' ' . $label)) return;

        $sub_fields = (array) ($field['sub_fields'] ?? array());
        if (($field['type'] ?? '') === 'flexible_content' && !empty($field['layouts']) && is_array($field['layouts']) && is_array($value)) {
            $layouts = array();
            foreach ($field['layouts'] as $layout) {
                if (is_array($layout) && !empty($layout['name'])) $layouts[(string) $layout['name']] = $layout;
            }
            foreach ($value as $row) {
                if (!is_array($row)) continue;
                $layout = $layouts[(string) ($row['acf_fc_layout'] ?? '')] ?? array();
                foreach ((array) ($layout['sub_fields'] ?? array()) as $sub_field) {
                    $sub_name = (string) ($sub_field['name'] ?? '');
                    $sub_key = (string) ($sub_field['key'] ?? '');
                    self::collect_acf_value((array) $sub_field, $row[$sub_name] ?? $row[$sub_key] ?? null, $parts, $depth + 1);
                }
            }
            return;
        }
        if ($sub_fields && is_array($value)) {
            $rows = array_is_list($value) ? $value : array($value);
            foreach ($rows as $row) {
                if (!is_array($row)) continue;
                foreach ($sub_fields as $sub_field) {
                    $sub_name = (string) ($sub_field['name'] ?? '');
                    $sub_key = (string) ($sub_field['key'] ?? '');
                    self::collect_acf_value((array) $sub_field, $row[$sub_name] ?? $row[$sub_key] ?? null, $parts, $depth + 1);
                }
            }
            return;
        }

        if (!self::selected('acf_fields', $key, self::is_textual_type((string) ($field['type'] ?? '')))) return;
        if (is_array($value)) {
            $value = implode(', ', array_filter(array_map(static fn($item): string => is_scalar($item) ? (string) $item : '', $value)));
        }
        if (is_scalar($value)) $value = (string) $value;
        if (!is_string($value)) return;
        $text = trim(wp_strip_all_tags(strip_shortcodes($value)));
        if ($text === '' || filter_var($text, FILTER_VALIDATE_URL) || preg_match('/^[a-f0-9]{24,}$/i', $text)) return;
        $parts[] = sanitize_text_field($label) . ': ' . self::limit($text, 900);
    }

    public static function seo_text_for_post(int $post_id): string
    {
        $parts = array();
        foreach (self::seo_sources() as $key => $source) {
            if (!self::selected('seo', $key, (bool) $source['detected'])) continue;
            foreach ($source['fields'] as $meta_key) {
                $value = trim((string) get_post_meta($post_id, $meta_key, true));
                if ($value !== '') $parts[] = $source['label'] . ': ' . self::limit(wp_strip_all_tags($value), 500);
            }
        }
        return implode("\n", array_values(array_unique($parts)));
    }

    public static function is_textual_type(string $type): bool
    {
        return in_array($type, array('text', 'textarea', 'wysiwyg', 'select', 'checkbox', 'radio', 'button_group', 'true_false', 'number'), true);
    }

    public static function is_sensitive(string $value): bool
    {
        return (bool) preg_match('/(?:password|passwd|secret|token|api[_ -]?key|private|credential|email|e-mail|phone|telefoon)/i', $value);
    }

    private static function limit(string $value, int $max): string
    {
        return function_exists('mb_substr') ? mb_substr($value, 0, $max) : substr($value, 0, $max);
    }
}
