<?php

namespace Tempel;

class User_Switching
{
    private const SWITCH_ACTION = 'tmpl_switch_user_';
    private const SWITCH_BACK_ACTION = 'tmpl_switch_back_';
    private const COOKIE_PREFIX = 'tempel_user_switch_';
    private const TOKEN_LIFETIME = 8 * HOUR_IN_SECONDS;

    public function __construct()
    {
        add_filter('user_row_actions', array($this, 'add_user_action'), 10, 2);
        add_action('admin_post_tmpl_switch_user', array($this, 'switch_user'));
        add_action('admin_post_tmpl_switch_back', array($this, 'switch_back'));
        add_action('admin_bar_menu', array($this, 'add_switch_back_node'), 999);
        add_action('wp_logout', array($this, 'clear_switch_token'), 10, 0);
    }

    public function add_user_action(array $actions, \WP_User $user): array
    {
        if (!$this->can_switch_to($user)) {
            return $actions;
        }

        $url = wp_nonce_url(
            add_query_arg(
                array(
                    'action' => 'tmpl_switch_user',
                    'user' => $user->ID,
                ),
                admin_url('admin-post.php')
            ),
            self::SWITCH_ACTION . $user->ID
        );

        $actions['tmpl_switch_user'] = '<a href="' . esc_url($url) . '">' . esc_html__('Inloggen als', 'tempel-settings') . '</a>';

        return $actions;
    }

    public function switch_user(): void
    {
        $target_id = isset($_GET['user']) ? absint($_GET['user']) : 0;
        check_admin_referer(self::SWITCH_ACTION . $target_id);

        $target = get_user_by('id', $target_id);

        if (!$target || !$this->can_switch_to($target)) {
            wp_die(esc_html__('Je hebt geen rechten om naar deze gebruiker te wisselen.', 'tempel-settings'));
        }

        $original = wp_get_current_user();
        $token = wp_generate_password(48, false, false);

        set_transient(
            $this->get_token_key($token),
            array(
                'original_user_id' => $original->ID,
                'target_user_id' => $target->ID,
            ),
            self::TOKEN_LIFETIME
        );
        $this->set_switch_cookie($token, time() + self::TOKEN_LIFETIME);

        wp_clear_auth_cookie();
        wp_set_current_user($target->ID, $target->user_login);
        wp_set_auth_cookie($target->ID, false, is_ssl());
        do_action('wp_login', $target->user_login, $target);

        wp_safe_redirect(admin_url(), 302, 'Tempel Settings User Switching');
        exit;
    }

    public function switch_back(): void
    {
        $current_user_id = get_current_user_id();
        check_admin_referer(self::SWITCH_BACK_ACTION . $current_user_id);

        $switch = $this->get_switch_data();

        if (!$switch || (int) $switch['target_user_id'] !== $current_user_id) {
            wp_die(esc_html__('De gebruikerswissel is verlopen of ongeldig.', 'tempel-settings'));
        }

        $original = get_user_by('id', (int) $switch['original_user_id']);

        if (!$original) {
            $this->clear_switch_token();
            wp_die(esc_html__('De oorspronkelijke gebruiker bestaat niet meer.', 'tempel-settings'));
        }

        $this->clear_switch_token();
        wp_clear_auth_cookie();
        wp_set_current_user($original->ID, $original->user_login);
        wp_set_auth_cookie($original->ID, false, is_ssl());
        do_action('wp_login', $original->user_login, $original);

        wp_safe_redirect(admin_url('users.php'), 302, 'Tempel Settings User Switching');
        exit;
    }

    public function add_switch_back_node(\WP_Admin_Bar $admin_bar): void
    {
        $switch = $this->get_switch_data();

        if (!$switch || (int) $switch['target_user_id'] !== get_current_user_id()) {
            return;
        }

        $original = get_user_by('id', (int) $switch['original_user_id']);

        if (!$original) {
            return;
        }

        $url = wp_nonce_url(
            admin_url('admin-post.php?action=tmpl_switch_back'),
            self::SWITCH_BACK_ACTION . get_current_user_id()
        );

        $admin_bar->add_node(array(
            'id' => 'tempel-switch-back',
            'title' => sprintf(__('Terug naar %s', 'tempel-settings'), $original->display_name),
            'href' => $url,
        ));
    }

    public function clear_switch_token(): void
    {
        $token = $this->get_cookie_token();

        if ($token !== '') {
            delete_transient($this->get_token_key($token));
        }

        $this->set_switch_cookie('', time() - YEAR_IN_SECONDS);
        unset($_COOKIE[$this->get_cookie_name()]);
    }

    private function can_switch_to(\WP_User $target): bool
    {
        $current_user_id = get_current_user_id();

        if (
            !$current_user_id ||
            $current_user_id === $target->ID ||
            !current_user_can('edit_users') ||
            !current_user_can('edit_user', $target->ID)
        ) {
            return false;
        }

        return !is_multisite() || !is_super_admin($target->ID) || is_super_admin($current_user_id);
    }

    private function get_switch_data(): ?array
    {
        $token = $this->get_cookie_token();

        if ($token === '') {
            return null;
        }

        $data = get_transient($this->get_token_key($token));

        if (!is_array($data) || empty($data['original_user_id']) || empty($data['target_user_id'])) {
            return null;
        }

        return $data;
    }

    private function get_cookie_token(): string
    {
        if (!isset($_COOKIE[$this->get_cookie_name()])) {
            return '';
        }

        return sanitize_text_field(wp_unslash($_COOKIE[$this->get_cookie_name()]));
    }

    private function get_token_key(string $token): string
    {
        return 'tmpl_user_switch_' . hash_hmac('sha256', $token, wp_salt('auth'));
    }

    private function get_cookie_name(): string
    {
        return self::COOKIE_PREFIX . COOKIEHASH;
    }

    private function set_switch_cookie(string $value, int $expires): void
    {
        $options = array(
            'expires' => $expires,
            'path' => COOKIEPATH ?: '/',
            'domain' => COOKIE_DOMAIN,
            'secure' => is_ssl(),
            'httponly' => true,
            'samesite' => 'Lax',
        );
        setcookie($this->get_cookie_name(), $value, $options);

        if (COOKIEPATH !== SITECOOKIEPATH) {
            $options['path'] = SITECOOKIEPATH ?: '/';
            setcookie($this->get_cookie_name(), $value, $options);
        }
    }
}
