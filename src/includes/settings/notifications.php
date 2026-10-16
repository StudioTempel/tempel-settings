<?php

namespace Tempel;

class Notifications
{
    private const POST_TYPE = 'tempel_notification';
    public function __construct()
    {
        if (did_action('init')) {
            $this->register_post_type();
        } else {
            add_action('init', array($this, 'register_post_type'));
        }

        if (did_action('acf/init')) {
            $this->register_fields();
        } else {
            add_action('acf/init', array($this, 'register_fields'));
        }

        add_action('wp_enqueue_scripts', array($this, 'enqueue_assets'));
        add_action('wp_footer', array($this, 'render'), 5);
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_assets'));
        add_action('wp_ajax_tempel_sort_notifications', array($this, 'save_order'));
        add_action('wp_ajax_tempel_toggle_notification', array($this, 'toggle_active'));
        add_action('pre_get_posts', array($this, 'order_admin_overview'));
        add_filter('manage_' . self::POST_TYPE . '_posts_columns', array($this, 'add_admin_columns'));
        add_action('manage_' . self::POST_TYPE . '_posts_custom_column', array($this, 'render_admin_column'), 10, 2);
        add_action('acf/render_field/key=field_tempel_notification_reset', array($this, 'render_frequency_reset'));
        add_action('admin_post_tempel_reset_notification_frequency', array($this, 'reset_frequency'));
        add_action('admin_notices', array($this, 'render_reset_notice'));
    }

    public function register_post_type(): void
    {
        register_post_type(self::POST_TYPE, array(
            'labels' => array(
                'name' => __('Meldingen', 'tempel-settings'),
                'singular_name' => __('Melding', 'tempel-settings'),
                'add_new_item' => __('Nieuwe melding', 'tempel-settings'),
                'edit_item' => __('Melding bewerken', 'tempel-settings'),
                'new_item' => __('Nieuwe melding', 'tempel-settings'),
                'view_item' => __('Melding bekijken', 'tempel-settings'),
                'search_items' => __('Meldingen zoeken', 'tempel-settings'),
                'not_found' => __('Geen meldingen gevonden.', 'tempel-settings'),
                'menu_name' => __('Meldingen', 'tempel-settings'),
            ),
            'public' => false,
            'show_ui' => true,
            'show_in_menu' => true,
            'show_in_rest' => false,
            'menu_icon' => 'dashicons-megaphone',
            'menu_position' => 98,
            'supports' => array('title'),
            'capabilities' => array_fill_keys(array(
                'edit_post', 'read_post', 'delete_post', 'edit_posts', 'edit_others_posts',
                'publish_posts', 'read_private_posts', 'delete_posts', 'delete_private_posts',
                'delete_published_posts', 'delete_others_posts', 'edit_private_posts',
                'edit_published_posts', 'create_posts',
            ), 'manage_options'),
            'map_meta_cap' => false,
        ));
    }

    public function register_fields(): void
    {
        if (!function_exists('acf_add_local_field_group')) {
            return;
        }

        acf_add_local_field_group(array(
            'key' => 'group_tempel_notification',
            'title' => __('Melding instellen', 'tempel-settings'),
            'fields' => array(
                array('key' => 'field_tempel_notification_tab_content', 'label' => __('Inhoud', 'tempel-settings'), 'type' => 'tab', 'placement' => 'top'),
                array('key' => 'field_tempel_notification_active', 'label' => __('Actief', 'tempel-settings'), 'name' => 'tempel_notification_active', 'type' => 'true_false', 'ui' => 1, 'ui_on_text' => '', 'ui_off_text' => '', 'default_value' => 0),
                array('key' => 'field_tempel_notification_content', 'label' => __('Inhoud', 'tempel-settings'), 'name' => 'tempel_notification_content', 'type' => 'wysiwyg', 'required' => 1, 'tabs' => 'all', 'toolbar' => 'full', 'media_upload' => 1),
                array('key' => 'field_tempel_notification_button', 'label' => __('Knop', 'tempel-settings'), 'name' => 'tempel_notification_button', 'type' => 'link', 'return_format' => 'array'),
                array('key' => 'field_tempel_notification_tab_settings', 'label' => __('Instellingen', 'tempel-settings'), 'type' => 'tab', 'placement' => 'top'),
                array('key' => 'field_tempel_notification_position', 'label' => __('Positie', 'tempel-settings'), 'name' => 'tempel_notification_position', 'type' => 'select', 'choices' => array('center' => __('Midden als popup', 'tempel-settings'), 'top' => __('Boven', 'tempel-settings'), 'bottom' => __('Onder', 'tempel-settings')), 'default_value' => 'center', 'return_format' => 'value'),
                array('key' => 'field_tempel_notification_compact', 'label' => __('Compacte balk', 'tempel-settings'), 'name' => 'tempel_notification_compact', 'type' => 'true_false', 'instructions' => __('Plaatst tekst, knop en sluitknop compact naast elkaar.', 'tempel-settings'), 'ui' => 1, 'ui_on_text' => '', 'ui_off_text' => '', 'default_value' => 0, 'conditional_logic' => array(array(array('field' => 'field_tempel_notification_position', 'operator' => '!=', 'value' => 'center')))),
                array('key' => 'field_tempel_notification_backdrop', 'label' => __('Donkere achtergrond tonen', 'tempel-settings'), 'name' => 'tempel_notification_backdrop', 'type' => 'true_false', 'ui' => 1, 'ui_on_text' => '', 'ui_off_text' => '', 'default_value' => 1, 'conditional_logic' => array(array(array('field' => 'field_tempel_notification_position', 'operator' => '==', 'value' => 'center')))),
                array('key' => 'field_tempel_notification_dismissible', 'label' => __('Sluitknop tonen', 'tempel-settings'), 'name' => 'tempel_notification_dismissible', 'type' => 'true_false', 'ui' => 1, 'ui_on_text' => '', 'ui_off_text' => '', 'default_value' => 1),
                array('key' => 'field_tempel_notification_delay', 'label' => __('Vertraging (seconden)', 'tempel-settings'), 'name' => 'tempel_notification_delay', 'type' => 'number', 'default_value' => 0, 'min' => 0, 'max' => 300, 'step' => 1),
                array('key' => 'field_tempel_notification_mobile', 'label' => __('Op mobiel tonen', 'tempel-settings'), 'name' => 'tempel_notification_mobile', 'type' => 'true_false', 'ui' => 1, 'ui_on_text' => '', 'ui_off_text' => '', 'default_value' => 1),
                array('key' => 'field_tempel_notification_start', 'label' => __('Startdatum en -tijd', 'tempel-settings'), 'name' => 'tempel_notification_start', 'type' => 'date_time_picker', 'display_format' => 'd-m-Y H:i', 'return_format' => 'Y-m-d H:i', 'first_day' => 1, 'wrapper' => array('width' => '50')),
                array('key' => 'field_tempel_notification_end', 'label' => __('Einddatum en -tijd', 'tempel-settings'), 'name' => 'tempel_notification_end', 'type' => 'date_time_picker', 'display_format' => 'd-m-Y H:i', 'return_format' => 'Y-m-d H:i', 'first_day' => 1, 'wrapper' => array('width' => '50')),
                array('key' => 'field_tempel_notification_location', 'label' => __('Locatie', 'tempel-settings'), 'name' => 'tempel_notification_location', 'type' => 'select', 'choices' => array('everywhere' => __('Overal tonen', 'tempel-settings'), 'specific' => __('Alleen op specifieke URL’s', 'tempel-settings')), 'default_value' => 'everywhere', 'return_format' => 'value'),
                array('key' => 'field_tempel_notification_include_urls', 'label' => __('URL’s opnemen', 'tempel-settings'), 'name' => 'tempel_notification_include_urls', 'type' => 'textarea', 'instructions' => __('Eén URL of pad per regel. Een * werkt als jokerteken, bijvoorbeeld /diensten/*.', 'tempel-settings'), 'rows' => 4, 'conditional_logic' => array(array(array('field' => 'field_tempel_notification_location', 'operator' => '==', 'value' => 'specific')))),
                array('key' => 'field_tempel_notification_exclude_urls', 'label' => __('URL’s uitsluiten', 'tempel-settings'), 'name' => 'tempel_notification_exclude_urls', 'type' => 'textarea', 'instructions' => __('Eén URL of pad per regel. Deze regels gaan voor op opgenomen URL’s.', 'tempel-settings'), 'rows' => 4),
                array('key' => 'field_tempel_notification_frequency', 'label' => __('Frequentie', 'tempel-settings'), 'name' => 'tempel_notification_frequency', 'type' => 'select', 'choices' => array('always' => __('Iedere keer tonen', 'tempel-settings'), 'once' => __('Eenmalig per browser', 'tempel-settings'), 'days' => __('Opnieuw tonen na een aantal dagen', 'tempel-settings')), 'default_value' => 'always', 'return_format' => 'value'),
                array('key' => 'field_tempel_notification_cookie_days', 'label' => __('Opnieuw tonen na (dagen)', 'tempel-settings'), 'name' => 'tempel_notification_cookie_days', 'type' => 'number', 'default_value' => 30, 'min' => 1, 'max' => 3650, 'step' => 1, 'conditional_logic' => array(array(array('field' => 'field_tempel_notification_frequency', 'operator' => '==', 'value' => 'days')))),
                array('key' => 'field_tempel_notification_reset', 'label' => __('Frequentie resetten', 'tempel-settings'), 'type' => 'message', 'message' => ''),
            ),
            'location' => array(array(array('param' => 'post_type', 'operator' => '==', 'value' => self::POST_TYPE))),
            'position' => 'normal',
            'style' => 'seamless',
            'label_placement' => 'top',
            'instruction_placement' => 'label',
            'active' => true,
        ));
    }

    public function enqueue_assets(): void
    {
        wp_enqueue_style('tempel-notifications', TEMPEL_SETTINGS_ASSET_URL . 'css/notifications.css', array(), TEMPEL_SETTINGS_VERSION);
        wp_enqueue_script('tempel-notifications', TEMPEL_SETTINGS_ASSET_URL . 'js/notifications.js', array(), TEMPEL_SETTINGS_VERSION, true);
    }

    public function enqueue_admin_assets(string $hook): void
    {
        $screen = function_exists('get_current_screen') ? get_current_screen() : null;
        if ($hook !== 'edit.php' || !$screen || $screen->post_type !== self::POST_TYPE) {
            return;
        }

        wp_enqueue_script('jquery-ui-sortable');
        wp_enqueue_style('tempel-notifications-admin', TEMPEL_SETTINGS_ASSET_URL . 'css/notifications-admin.css', array(), TEMPEL_SETTINGS_VERSION);
        wp_enqueue_script('tempel-notifications-admin', TEMPEL_SETTINGS_ASSET_URL . 'js/notifications-admin.js', array('jquery', 'jquery-ui-sortable'), TEMPEL_SETTINGS_VERSION, true);
        wp_localize_script('tempel-notifications-admin', 'tempelNotificationsAdmin', array(
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('tempel_sort_notifications'),
            'toggleNonce' => wp_create_nonce('tempel_toggle_notification'),
            'error' => __('De volgorde kon niet worden opgeslagen.', 'tempel-settings'),
            'toggleError' => __('De status kon niet worden gewijzigd.', 'tempel-settings'),
            'activate' => __('Activeren', 'tempel-settings'),
            'deactivate' => __('Deactiveren', 'tempel-settings'),
        ));
    }

    public function add_admin_columns(array $columns): array
    {
        $result = array();

        foreach ($columns as $key => $label) {
            $result[$key] = $label;
            if ($key === 'title') {
                $result['tempel_notification_active'] = __('Actief', 'tempel-settings');
                $result['tempel_notification_position'] = __('Positie', 'tempel-settings');
                $result['tempel_notification_frequency'] = __('Frequentie', 'tempel-settings');
            }
        }

        return $result;
    }

    public function render_admin_column(string $column, int $post_id): void
    {
        if ($column === 'tempel_notification_active') {
            $active = (bool) get_field('tempel_notification_active', $post_id);
            $label = $active ? __('Deactiveren', 'tempel-settings') : __('Activeren', 'tempel-settings');
            echo '<button type="button" class="tempel-notification-toggle' . ($active ? ' is-active' : '') . '" data-post-id="' . esc_attr($post_id) . '" aria-pressed="' . ($active ? 'true' : 'false') . '" aria-label="' . esc_attr($label) . '"><span aria-hidden="true"></span></button>';
            return;
        }

        if ($column === 'tempel_notification_position') {
            $position = (string) (get_field('tempel_notification_position', $post_id) ?: 'center');
            $labels = array(
                'center' => __('Popup', 'tempel-settings'),
                'top' => __('Boven', 'tempel-settings'),
                'bottom' => __('Onder', 'tempel-settings'),
            );
            echo esc_html($labels[$position] ?? $labels['center']);
            return;
        }

        if ($column === 'tempel_notification_frequency') {
            $frequency = (string) (get_field('tempel_notification_frequency', $post_id) ?: 'always');
            if ($frequency === 'once') {
                echo esc_html__('Eenmalig', 'tempel-settings');
                return;
            }
            if ($frequency === 'days') {
                $days = max(1, min(3650, absint(get_field('tempel_notification_cookie_days', $post_id) ?: 30)));
                printf(esc_html(_n('Iedere %d dag', 'Iedere %d dagen', $days, 'tempel-settings')), $days);
                return;
            }

            echo esc_html__('Iedere keer', 'tempel-settings');
        }
    }

    public function toggle_active(): void
    {
        check_ajax_referer('tempel_toggle_notification', 'nonce');

        $post_id = isset($_POST['post_id']) ? absint($_POST['post_id']) : 0;
        if (!$post_id || get_post_type($post_id) !== self::POST_TYPE || !current_user_can('manage_options')) {
            wp_send_json_error(null, 403);
        }

        $active = !((bool) get_field('tempel_notification_active', $post_id));
        update_field('tempel_notification_active', $active ? 1 : 0, $post_id);

        wp_send_json_success(array('active' => $active));
    }

    public function save_order(): void
    {
        check_ajax_referer('tempel_sort_notifications', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(null, 403);
        }

        $post_ids = isset($_POST['post_ids']) ? (array) wp_unslash($_POST['post_ids']) : array();
        foreach (array_values(array_unique(array_map('absint', $post_ids))) as $position => $post_id) {
            if (get_post_type($post_id) !== self::POST_TYPE) {
                continue;
            }

            wp_update_post(array('ID' => $post_id, 'menu_order' => $position));
        }

        wp_send_json_success();
    }

    public function render_frequency_reset(): void
    {
        $post_id = isset($_GET['post']) ? absint($_GET['post']) : 0;

        if (!$post_id || get_post_type($post_id) !== self::POST_TYPE) {
            echo '<p>' . esc_html__('Sla de melding eerst op om de frequentie te kunnen resetten.', 'tempel-settings') . '</p>';
            return;
        }

        $url = wp_nonce_url(
            add_query_arg(array('action' => 'tempel_reset_notification_frequency', 'post_id' => $post_id), admin_url('admin-post.php')),
            'tempel_reset_notification_frequency_' . $post_id
        );

        echo '<a class="button" href="' . esc_url($url) . '">' . esc_html__('Frequentie resetten', 'tempel-settings') . '</a>';
    }

    public function reset_frequency(): void
    {
        $post_id = isset($_GET['post_id']) ? absint($_GET['post_id']) : 0;

        if (!$post_id || get_post_type($post_id) !== self::POST_TYPE || !current_user_can('manage_options')) {
            wp_die(esc_html__('Je hebt geen toestemming om deze frequentie te resetten.', 'tempel-settings'));
        }

        check_admin_referer('tempel_reset_notification_frequency_' . $post_id);
        update_post_meta($post_id, '_tempel_notification_frequency_version', time());

        wp_safe_redirect(add_query_arg(array('post' => $post_id, 'action' => 'edit', 'tempel_frequency_reset' => '1'), admin_url('post.php')));
        exit;
    }

    public function render_reset_notice(): void
    {
        $reset = isset($_GET['tempel_frequency_reset']) ? sanitize_key(wp_unslash($_GET['tempel_frequency_reset'])) : '';
        if ($reset !== '1') {
            return;
        }

        echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__('De frequentie is gereset. De melding kan opnieuw worden getoond.', 'tempel-settings') . '</p></div>';
    }

    public function order_admin_overview($query): void
    {
        if (!is_admin() || !$query->is_main_query() || $query->get('post_type') !== self::POST_TYPE || $query->get('orderby')) {
            return;
        }

        $query->set('posts_per_page', -1);
        $query->set('orderby', array('menu_order' => 'ASC', 'date' => 'DESC'));
    }

    public function render(): void
    {
        if (!function_exists('get_field')) {
            return;
        }

        $notifications = get_posts(array(
            'post_type' => self::POST_TYPE,
            'post_status' => 'publish',
            'posts_per_page' => -1,
            'orderby' => array('menu_order' => 'ASC', 'date' => 'DESC'),
            'suppress_filters' => false,
        ));
        foreach ($notifications as $notification) {
            if (!$this->is_visible($notification->ID)) {
                continue;
            }

            $this->render_notification($notification->ID);
        }
    }

    private function is_visible(int $post_id): bool
    {
        if (!get_field('tempel_notification_active', $post_id)) {
            return false;
        }

        if (!self::is_within_schedule((string) get_field('tempel_notification_start', $post_id), (string) get_field('tempel_notification_end', $post_id), current_time('timestamp'))) {
            return false;
        }

        $request_uri = isset($_SERVER['REQUEST_URI']) ? wp_unslash($_SERVER['REQUEST_URI']) : '/';
        $path = (string) (wp_parse_url($request_uri, PHP_URL_PATH) ?: '/');
        $full_url = home_url($request_uri);
        $excluded = self::parse_patterns((string) get_field('tempel_notification_exclude_urls', $post_id));
        if (self::matches_patterns($path, $full_url, $excluded)) {
            return false;
        }

        $location = (string) (get_field('tempel_notification_location', $post_id) ?: 'everywhere');
        $included = self::parse_patterns((string) get_field('tempel_notification_include_urls', $post_id));

        return $location !== 'specific' || self::matches_patterns($path, $full_url, $included);
    }

    private function render_notification(int $post_id): void
    {
        $position = (string) (get_field('tempel_notification_position', $post_id) ?: 'center');
        $position = in_array($position, array('center', 'top', 'bottom'), true) ? $position : 'center';
        $frequency = (string) (get_field('tempel_notification_frequency', $post_id) ?: 'always');
        $frequency = in_array($frequency, array('always', 'once', 'days'), true) ? $frequency : 'always';
        $cookie_days = $frequency === 'once' ? 3650 : max(1, min(3650, absint(get_field('tempel_notification_cookie_days', $post_id) ?: 30)));
        $frequency_version = absint(get_post_meta($post_id, '_tempel_notification_frequency_version', true));
        $button = get_field('tempel_notification_button', $post_id);
        $classes = array('tempel-notification', 'tempel-notification--' . $position);
        if ($position !== 'center' && (bool) get_field('tempel_notification_compact', $post_id)) {
            $classes[] = 'tempel-notification--compact';
        }
        $dismissible = self::is_dismissible($position, (bool) get_field('tempel_notification_dismissible', $post_id));
        $backdrop = $position === 'center' && (bool) get_field('tempel_notification_backdrop', $post_id);
        $scope = get_field('tempel_notification_location', $post_id) === 'specific' ? 'specific' : 'everywhere';
        $content = (string) get_field('tempel_notification_content', $post_id);
        $accessible_title = get_the_title($post_id) ?: __('Melding', 'tempel-settings');
        ?>
        <?php if ($backdrop) : ?>
            <div class="tempel-notification-backdrop" data-tempel-notification-backdrop hidden></div>
        <?php endif; ?>
        <div class="<?php echo esc_attr(implode(' ', $classes)); ?>" data-tempel-notification data-cookie-key="<?php echo esc_attr($post_id . '_' . $frequency_version); ?>" data-frequency="<?php echo esc_attr($frequency); ?>" data-cookie-days="<?php echo esc_attr($cookie_days); ?>" data-delay="<?php echo esc_attr(max(0, min(300, absint(get_field('tempel_notification_delay', $post_id))))); ?>" data-mobile="<?php echo get_field('tempel_notification_mobile', $post_id) ? '1' : '0'; ?>" data-scope="<?php echo esc_attr($scope); ?>" data-dismissible="<?php echo $dismissible ? '1' : '0'; ?>" role="dialog" aria-modal="<?php echo $position === 'center' ? 'true' : 'false'; ?>" aria-labelledby="tempel-notification-title-<?php echo esc_attr($post_id); ?>" hidden>
            <div class="tempel-notification__inner">
                <h2 id="tempel-notification-title-<?php echo esc_attr($post_id); ?>" class="screen-reader-text"><?php echo esc_html($accessible_title); ?></h2>
                <?php if ($dismissible) : ?><button type="button" class="tempel-notification__close" data-tempel-notification-close aria-label="<?php esc_attr_e('Melding sluiten', 'tempel-settings'); ?>">&times;</button><?php endif; ?>
                <div class="tempel-notification__content"><?php echo wp_kses_post(apply_filters('the_content', $content)); ?></div>
                <?php if (is_array($button) && !empty($button['url']) && !empty($button['title'])) : ?>
                    <?php $target = ($button['target'] ?? '') === '_blank' ? '_blank' : '_self'; ?>
                    <a class="button tempel-notification__button" href="<?php echo esc_url($button['url']); ?>" target="<?php echo esc_attr($target); ?>"<?php echo $target === '_blank' ? ' rel="noopener noreferrer"' : ''; ?>><?php echo esc_html($button['title']); ?></a>
                <?php endif; ?>
            </div>
        </div>
        <?php
    }

    public static function parse_patterns(string $patterns): array
    {
        return array_values(array_filter(array_map('trim', preg_split('/\R/', $patterns) ?: array())));
    }

    public static function matches_patterns(string $path, string $full_url, array $patterns): bool
    {
        foreach ($patterns as $pattern) {
            $subject = preg_match('#^https?://#i', $pattern) ? $full_url : $path;
            $regex = '#^' . str_replace('\\*', '.*', preg_quote($pattern, '#')) . '$#i';
            if (preg_match($regex, $subject)) {
                return true;
            }
        }

        return false;
    }

    public static function is_within_schedule(string $start, string $end, int $now): bool
    {
        $timezone = function_exists('wp_timezone') ? wp_timezone() : new \DateTimeZone('UTC');

        try {
            $start_timestamp = $start !== '' ? (new \DateTimeImmutable($start, $timezone))->getTimestamp() : 0;
            $end_timestamp = $end !== '' ? (new \DateTimeImmutable($end, $timezone))->getTimestamp() : 0;
        } catch (\Exception $exception) {
            return false;
        }

        return (!$start_timestamp || $now >= $start_timestamp) && (!$end_timestamp || $now <= $end_timestamp);
    }

    public static function is_dismissible(string $position, bool $setting): bool
    {
        return $setting;
    }
}
