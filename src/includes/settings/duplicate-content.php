<?php

namespace Tempel;

class Duplicate_Content
{
    private const POST_NONCE_ACTION = 'tmpl_duplicate_post_';
    private const TERM_NONCE_ACTION = 'tmpl_duplicate_term_';
    private const MENU_NONCE_ACTION = 'tmpl_duplicate_menu_';

    public function __construct()
    {
        add_action('admin_init', array($this, 'register_taxonomy_actions'));
        add_filter('post_row_actions', array($this, 'add_post_action'), 10, 2);
        add_filter('page_row_actions', array($this, 'add_post_action'), 10, 2);
        add_action('admin_post_tmpl_duplicate_post', array($this, 'duplicate_post'));
        add_action('admin_post_tmpl_duplicate_term', array($this, 'duplicate_term'));
        add_action('admin_post_tmpl_duplicate_menu', array($this, 'duplicate_menu'));
        add_action('admin_footer-nav-menus.php', array($this, 'render_menu_button'));
    }

    public function register_taxonomy_actions(): void
    {
        foreach (get_taxonomies(array('show_ui' => true), 'names') as $taxonomy) {
            add_filter($taxonomy . '_row_actions', array($this, 'add_term_action'), 10, 2);
        }
    }

    public function add_post_action(array $actions, \WP_Post $post): array
    {
        if (!$this->supports_post_type($post->post_type) || !current_user_can('edit_post', $post->ID)) {
            return $actions;
        }

        $url = wp_nonce_url(
            admin_url('admin-post.php?action=tmpl_duplicate_post&post=' . $post->ID),
            self::POST_NONCE_ACTION . $post->ID
        );
        $actions['tmpl_duplicate'] = '<a href="' . esc_url($url) . '">' . esc_html__('Dupliceren', 'tempel-settings') . '</a>';

        return $actions;
    }

    public function add_term_action(array $actions, \WP_Term $term): array
    {
        $taxonomy = get_taxonomy($term->taxonomy);

        if (!$taxonomy || !current_user_can($taxonomy->cap->edit_terms)) {
            return $actions;
        }

        $url = wp_nonce_url(
            admin_url('admin-post.php?action=tmpl_duplicate_term&term=' . $term->term_id . '&taxonomy=' . $term->taxonomy),
            self::TERM_NONCE_ACTION . $term->term_id
        );
        $actions['tmpl_duplicate'] = '<a href="' . esc_url($url) . '">' . esc_html__('Dupliceren', 'tempel-settings') . '</a>';

        return $actions;
    }

    public function duplicate_post(): void
    {
        $post_id = isset($_GET['post']) ? absint($_GET['post']) : 0;
        check_admin_referer(self::POST_NONCE_ACTION . $post_id);

        $post = get_post($post_id);

        if (!$post || !$this->supports_post_type($post->post_type) || !current_user_can('edit_post', $post_id)) {
            wp_die(esc_html__('Je hebt geen rechten om deze content te dupliceren.', 'tempel-settings'));
        }

        $post_data = get_object_vars($post);
        unset($post_data['ID'], $post_data['guid'], $post_data['post_name'], $post_data['post_date'], $post_data['post_date_gmt'], $post_data['post_modified'], $post_data['post_modified_gmt'], $post_data['comment_count']);
        $post_data['post_status'] = 'draft';
        $post_data['post_author'] = get_current_user_id();
        $post_data['post_parent'] = $post->post_parent;

        $new_post_id = wp_insert_post(wp_slash($post_data), true);

        if (is_wp_error($new_post_id)) {
            wp_die(esc_html($new_post_id->get_error_message()));
        }

        $this->copy_post_meta($post_id, $new_post_id, array('_edit_lock', '_edit_last', '_wp_old_slug'));

        foreach (get_object_taxonomies($post->post_type) as $taxonomy) {
            $term_ids = wp_get_object_terms($post_id, $taxonomy, array('fields' => 'ids'));

            if (!is_wp_error($term_ids)) {
                wp_set_object_terms($new_post_id, $term_ids, $taxonomy);
            }
        }

        do_action('tmpl_content_duplicated', $new_post_id, $post_id, 'post');
        wp_safe_redirect(get_edit_post_link($new_post_id, 'raw'));
        exit;
    }

    public function duplicate_term(): void
    {
        $term_id = isset($_GET['term']) ? absint($_GET['term']) : 0;
        $taxonomy_name = isset($_GET['taxonomy']) ? sanitize_key(wp_unslash($_GET['taxonomy'])) : '';
        check_admin_referer(self::TERM_NONCE_ACTION . $term_id);

        $term = get_term($term_id, $taxonomy_name);
        $taxonomy = get_taxonomy($taxonomy_name);

        if (!$term || is_wp_error($term) || !$taxonomy || !current_user_can($taxonomy->cap->edit_terms)) {
            wp_die(esc_html__('Je hebt geen rechten om deze term te dupliceren.', 'tempel-settings'));
        }

        $name = $this->get_copy_name($term->name, function (string $candidate) use ($taxonomy_name): bool {
            return term_exists($candidate, $taxonomy_name) !== 0 && term_exists($candidate, $taxonomy_name) !== null;
        });
        $result = wp_insert_term($name, $taxonomy_name, array(
            'description' => $term->description,
            'parent' => (int) $term->parent,
        ));

        if (is_wp_error($result)) {
            wp_die(esc_html($result->get_error_message()));
        }

        foreach (get_term_meta($term_id) as $key => $values) {
            foreach ($values as $value) {
                add_term_meta($result['term_id'], $key, maybe_unserialize($value));
            }
        }

        do_action('tmpl_content_duplicated', $result['term_id'], $term_id, 'term');
        wp_safe_redirect(admin_url('term.php?taxonomy=' . rawurlencode($taxonomy_name) . '&tag_ID=' . $result['term_id'] . '&post_type=' . rawurlencode($this->get_taxonomy_post_type($taxonomy))));
        exit;
    }

    public function render_menu_button(): void
    {
        global $nav_menu_selected_id;

        $menu_id = isset($_REQUEST['menu']) ? absint($_REQUEST['menu']) : absint($nav_menu_selected_id ?? 0);

        if (!$menu_id || !current_user_can('edit_theme_options') || !wp_get_nav_menu_object($menu_id)) {
            return;
        }

        $url = wp_nonce_url(
            admin_url('admin-post.php?action=tmpl_duplicate_menu&menu=' . $menu_id),
            self::MENU_NONCE_ACTION . $menu_id
        );
        ?>
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const footer = document.querySelector('#nav-menu-footer .major-publishing-actions');
                const submit = footer ? footer.querySelector('.publishing-action') : null;
                if (!footer || !submit) return;

                const link = document.createElement('a');
                link.href = <?php echo wp_json_encode($url); ?>;
                link.className = 'button button-secondary';
                link.textContent = <?php echo wp_json_encode(__('Menu kopiëren', 'tempel-settings')); ?>;
                link.style.marginRight = '8px';
                submit.insertBefore(link, submit.firstChild);
            });
        </script>
        <?php
    }

    public function duplicate_menu(): void
    {
        $menu_id = isset($_GET['menu']) ? absint($_GET['menu']) : 0;
        check_admin_referer(self::MENU_NONCE_ACTION . $menu_id);

        $menu = wp_get_nav_menu_object($menu_id);

        if (!$menu || is_wp_error($menu) || !current_user_can('edit_theme_options')) {
            wp_die(esc_html__('Je hebt geen rechten om dit menu te dupliceren.', 'tempel-settings'));
        }

        $menu_name = $this->get_copy_name($menu->name, function (string $candidate): bool {
            return (bool) wp_get_nav_menu_object($candidate);
        });
        $new_menu_id = wp_create_nav_menu($menu_name);

        if (is_wp_error($new_menu_id)) {
            wp_die(esc_html($new_menu_id->get_error_message()));
        }

        $items = wp_get_nav_menu_items($menu_id, array('post_status' => 'any')) ?: array();
        $item_map = array();

        foreach ($items as $item) {
            $new_item_id = wp_update_nav_menu_item($new_menu_id, 0, array(
                'menu-item-db-id' => 0,
                'menu-item-object-id' => $item->object_id,
                'menu-item-object' => $item->object,
                'menu-item-type' => $item->type,
                'menu-item-title' => $item->post_title,
                'menu-item-url' => $item->url,
                'menu-item-description' => $item->description,
                'menu-item-attr-title' => $item->attr_title,
                'menu-item-target' => $item->target,
                'menu-item-classes' => implode(' ', (array) $item->classes),
                'menu-item-xfn' => $item->xfn,
                'menu-item-position' => $item->menu_order,
                'menu-item-status' => 'publish',
                'menu-item-parent-id' => 0,
            ));

            if (!is_wp_error($new_item_id)) {
                $item_map[$item->ID] = $new_item_id;
                $this->copy_post_meta($item->ID, $new_item_id, array(
                    '_menu_item_type', '_menu_item_menu_item_parent', '_menu_item_object_id',
                    '_menu_item_object', '_menu_item_target', '_menu_item_classes',
                    '_menu_item_xfn', '_menu_item_url', '_menu_item_orphaned',
                ));
            }
        }

        foreach ($items as $item) {
            if (empty($item_map[$item->ID]) || empty($item->menu_item_parent) || empty($item_map[$item->menu_item_parent])) {
                continue;
            }

            update_post_meta($item_map[$item->ID], '_menu_item_menu_item_parent', $item_map[$item->menu_item_parent]);
        }

        do_action('tmpl_content_duplicated', $new_menu_id, $menu_id, 'nav_menu');
        wp_safe_redirect(admin_url('nav-menus.php?action=edit&menu=' . $new_menu_id));
        exit;
    }

    private function supports_post_type(string $post_type): bool
    {
        $object = get_post_type_object($post_type);
        $excluded = array('attachment', 'revision', 'nav_menu_item', 'custom_css', 'customize_changeset', 'oembed_cache', 'user_request');
        $supported = $object && $object->show_ui && !in_array($post_type, $excluded, true);

        return (bool) apply_filters('tmpl_duplicate_supports_post_type', $supported, $post_type, $object);
    }

    private function copy_post_meta(int $source_id, int $target_id, array $excluded_keys = array()): void
    {
        foreach (get_post_meta($source_id) as $key => $values) {
            if (in_array($key, $excluded_keys, true)) {
                continue;
            }

            foreach ($values as $value) {
                add_post_meta($target_id, $key, maybe_unserialize($value));
            }
        }
    }

    private function get_copy_name(string $original, callable $exists): string
    {
        $number = 1;

        do {
            $candidate = sprintf(__('%1$s – kopie %2$d', 'tempel-settings'), $original, $number);
            $number++;
        } while ($exists($candidate));

        return $candidate;
    }

    private function get_taxonomy_post_type(\WP_Taxonomy $taxonomy): string
    {
        return !empty($taxonomy->object_type) ? (string) reset($taxonomy->object_type) : 'post';
    }
}
