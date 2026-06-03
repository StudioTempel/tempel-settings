<?php

namespace Tempel;

class Taxonomy_Order
{
    private string $meta_key = '_tmpl_taxonomy_order';

    public function __construct()
    {
        add_action('admin_enqueue_scripts', array($this, 'enqueue_assets'));
        add_action('wp_ajax_tmpl_save_taxonomy_order', array($this, 'save_taxonomy_order'));
        add_filter('get_terms', array($this, 'sort_admin_terms_list'), 10, 4);
    }

    public function enqueue_assets(): void
    {
        $screen = get_current_screen();

        if (!$screen || $screen->base !== 'edit-tags') {
            return;
        }

        $taxonomy = $screen->taxonomy ?? '';

        if (!$this->supports_taxonomy($taxonomy)) {
            return;
        }

        $taxonomy_object = get_taxonomy($taxonomy);

        if (!$taxonomy_object || !current_user_can($taxonomy_object->cap->manage_terms)) {
            return;
        }

        wp_enqueue_style(
            'tmpl-taxonomy-order',
            TEMPEL_SETTINGS_ASSET_URL . 'css/taxonomy-order.css',
            array(),
            TEMPEL_SETTINGS_VERSION
        );

        wp_enqueue_script(
            'tmpl-taxonomy-order',
            TEMPEL_SETTINGS_ASSET_URL . 'js/taxonomy-order.js',
            array('jquery', 'jquery-ui-sortable'),
            TEMPEL_SETTINGS_VERSION,
            true
        );

        wp_localize_script(
            'tmpl-taxonomy-order',
            'tmplTaxonomyOrder',
            array(
                'ajaxUrl'  => admin_url('admin-ajax.php'),
                'action'   => 'tmpl_save_taxonomy_order',
                'nonce'    => wp_create_nonce('tmpl_save_taxonomy_order'),
                'taxonomy' => $taxonomy,
            )
        );
    }

    public function save_taxonomy_order(): void
    {
        check_ajax_referer('tmpl_save_taxonomy_order', 'nonce');

        $taxonomy = isset($_POST['taxonomy']) ? sanitize_key(wp_unslash($_POST['taxonomy'])) : '';
        $order = isset($_POST['order']) ? array_map('absint', (array) wp_unslash($_POST['order'])) : array();

        if (!$this->supports_taxonomy($taxonomy)) {
            wp_send_json_error(
                array(
                    'message' => __('Deze taxonomie kan niet opnieuw worden gesorteerd.', 'tempel-settings'),
                ),
                400
            );
        }

        $taxonomy_object = get_taxonomy($taxonomy);

        if (!$taxonomy_object || !current_user_can($taxonomy_object->cap->manage_terms)) {
            wp_send_json_error(
                array(
                    'message' => __('Je hebt geen rechten om termen van deze taxonomie te sorteren.', 'tempel-settings'),
                ),
                403
            );
        }

        if (empty($order)) {
            wp_send_json_error(
                array(
                    'message' => __('Geen sorteervolgorde voor termen ontvangen.', 'tempel-settings'),
                ),
                400
            );
        }

        foreach ($order as $position => $term_id) {
            if ($term_id <= 0) {
                continue;
            }

            $term = get_term($term_id, $taxonomy);

            if ($term && !is_wp_error($term)) {
                update_term_meta($term_id, $this->meta_key, $position);
            }
        }

        wp_send_json_success();
    }

    public function sort_admin_terms_list(array $terms, array $taxonomies, array $args, \WP_Term_Query $term_query): array
    {
        $taxonomy = $this->get_sortable_taxonomy($terms, $taxonomies, $args);

        if (!$taxonomy) {
            return $terms;
        }

        $this->ensure_initial_order($terms);

        $order_map = array();

        foreach ($terms as $term) {
            $value = get_term_meta($term->term_id, $this->meta_key, true);
            $order_map[$term->term_id] = ($value === '') ? PHP_INT_MAX : (int) $value;
        }

        $grouped_terms = array();

        foreach ($terms as $term) {
            $parent = (int) $term->parent;

            if (!isset($grouped_terms[$parent])) {
                $grouped_terms[$parent] = array();
            }

            $grouped_terms[$parent][] = $term;
        }

        foreach ($grouped_terms as &$siblings) {
            usort($siblings, function ($left, $right) use ($order_map) {
                $left_order = $order_map[$left->term_id] ?? PHP_INT_MAX;
                $right_order = $order_map[$right->term_id] ?? PHP_INT_MAX;

                if ($left_order !== $right_order) {
                    return $left_order <=> $right_order;
                }

                return strcasecmp($left->name, $right->name);
            });
        }
        unset($siblings);

        $sorted = $this->flatten_term_tree($grouped_terms);

        if (count($sorted) !== count($terms)) {
            $sorted_ids = array_map(static function ($term) {
                return (int) $term->term_id;
            }, $sorted);

            foreach ($terms as $term) {
                if (!in_array((int) $term->term_id, $sorted_ids, true)) {
                    $sorted[] = $term;
                }
            }
        }

        return $sorted;
    }

    private function get_sortable_taxonomy(array $terms, array $taxonomies, array $args): string|false
    {
        if (
            !is_admin() ||
            empty($terms) ||
            empty($taxonomies) ||
            count($taxonomies) !== 1
        ) {
            return false;
        }

        $taxonomy = reset($taxonomies);

        if (!$this->supports_taxonomy($taxonomy)) {
            return false;
        }

        if (!empty($args['fields']) && $args['fields'] !== 'all') {
            return false;
        }

        if (isset($_REQUEST['orderby'])) {
            return false;
        }

        if (!function_exists('get_current_screen')) {
            return false;
        }

        $screen = get_current_screen();

        if (!$screen || $screen->base !== 'edit-tags' || $screen->taxonomy !== $taxonomy) {
            return false;
        }

        return $taxonomy;
    }

    private function flatten_term_tree(array $grouped_terms, int $parent = 0): array
    {
        $sorted = array();

        if (empty($grouped_terms[$parent])) {
            return $sorted;
        }

        foreach ($grouped_terms[$parent] as $term) {
            $sorted[] = $term;

            foreach ($this->flatten_term_tree($grouped_terms, (int) $term->term_id) as $child_term) {
                $sorted[] = $child_term;
            }
        }

        return $sorted;
    }

    private function ensure_initial_order(array $terms): void
    {
        foreach ($terms as $index => $term) {
            if (!metadata_exists('term', $term->term_id, $this->meta_key)) {
                update_term_meta($term->term_id, $this->meta_key, $index);
            }
        }
    }

    private function supports_taxonomy(string $taxonomy): bool
    {
        if (!$taxonomy) {
            return false;
        }

        if (!taxonomy_exists($taxonomy) || !is_taxonomy_hierarchical($taxonomy)) {
            return false;
        }

        $taxonomy_object = get_taxonomy($taxonomy);

        if (!$taxonomy_object) {
            return false;
        }

        return (bool) ($taxonomy_object->show_ui ?? false);
    }
}
