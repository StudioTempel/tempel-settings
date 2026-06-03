<?php

namespace Tempel;

class Magic_Login
{
    private string $request_action = 'tempel_magic_login';
    private string $verify_action = 'tempel_magic_login_verify';
    private string $meta_key = '_tempel_magic_login';

    public function __construct()
    {
        add_action('login_form_' . $this->request_action, array($this, 'render_request_form'));
        add_action('login_form_tempel_send_magic_link', array($this, 'send_magic_link'));
        add_action('login_form_' . $this->verify_action, array($this, 'verify_magic_link'));
        add_action('login_footer', array($this, 'render_login_link'));
    }

    public function render_login_link(): void
    {
        if (!$this->is_enabled()) {
            return;
        }

        if (isset($_GET['action']) && in_array(sanitize_key(wp_unslash($_GET['action'])), array($this->request_action, $this->verify_action), true)) {
            return;
        }

        $url = add_query_arg('action', $this->request_action, wp_login_url());
        ?>
        <p id="tempel-magic-login-link" class="tempel-magic-login-link">
            <a href="<?php echo esc_url($url); ?>"><?php esc_html_e('Inloggen met e-maillink', 'tempel-settings'); ?></a>
        </p>
        <?php
    }

    public function render_request_form(): void
    {
        if (!$this->is_enabled()) {
            wp_safe_redirect(wp_login_url());
            exit;
        }

        $message = isset($_GET['magic_link_sent'])
            ? __('Als dit e-mailadres bekend is, is er een inloglink verzonden.', 'tempel-settings')
            : '';

        login_header(__('Inloggen met e-maillink', 'tempel-settings'), $message);
        ?>
        <form name="tempelmagicloginform" id="tempelmagicloginform" action="<?php echo esc_url(site_url('wp-login.php?action=tempel_send_magic_link', 'login_post')); ?>" method="post">
            <?php wp_nonce_field('tempel_send_magic_link', 'tempel_magic_login_nonce'); ?>
            <p>
                <label for="user_email"><?php esc_html_e('E-mailadres', 'tempel-settings'); ?></label>
                <input type="email" name="user_email" id="user_email" class="input" value="" size="20" autocapitalize="off" autocomplete="email" required>
            </p>
            <?php
            $redirect_to = isset($_REQUEST['redirect_to']) ? esc_url_raw(wp_unslash($_REQUEST['redirect_to'])) : '';
            if ($redirect_to !== '') :
                ?>
                <input type="hidden" name="redirect_to" value="<?php echo esc_attr($redirect_to); ?>">
            <?php endif; ?>
            <p class="submit">
                <input type="submit" name="wp-submit" id="wp-submit" class="button button-primary button-large" value="<?php esc_attr_e('Stuur inloglink', 'tempel-settings'); ?>">
            </p>
        </form>
        <p id="nav">
            <a href="<?php echo esc_url(wp_login_url()); ?>"><?php esc_html_e('Terug naar wachtwoord-login', 'tempel-settings'); ?></a>
        </p>
        <?php
        login_footer('user_email');
        exit;
    }

    public function send_magic_link(): void
    {
        if (!$this->is_enabled()) {
            wp_safe_redirect(wp_login_url());
            exit;
        }

        if (
            !isset($_POST['tempel_magic_login_nonce']) ||
            !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['tempel_magic_login_nonce'])), 'tempel_send_magic_link')
        ) {
            wp_safe_redirect(add_query_arg('action', $this->request_action, wp_login_url()));
            exit;
        }

        $email = isset($_POST['user_email']) ? sanitize_email(wp_unslash($_POST['user_email'])) : '';
        $redirect_to = isset($_POST['redirect_to']) ? esc_url_raw(wp_unslash($_POST['redirect_to'])) : '';

        if ($email !== '' && is_email($email) && !$this->is_rate_limited($email)) {
            $user = get_user_by('email', $email);

            if ($user instanceof \WP_User && $this->is_user_allowed($user)) {
                $this->create_and_send_link($user, $redirect_to);
            }
        }

        wp_safe_redirect(add_query_arg(array(
            'action' => $this->request_action,
            'magic_link_sent' => '1',
        ), wp_login_url()));
        exit;
    }

    public function verify_magic_link(): void
    {
        if (!$this->is_enabled()) {
            wp_safe_redirect(wp_login_url());
            exit;
        }

        $user_id = isset($_GET['uid']) ? absint($_GET['uid']) : 0;
        $token = isset($_GET['token']) ? sanitize_text_field(wp_unslash($_GET['token'])) : '';
        $redirect_to = isset($_GET['redirect_to']) ? esc_url_raw(wp_unslash($_GET['redirect_to'])) : admin_url();

        $user = $user_id > 0 ? get_user_by('id', $user_id) : false;

        if (!$user instanceof \WP_User || !$this->is_user_allowed($user) || !$this->is_valid_token($user_id, $token)) {
            wp_safe_redirect(add_query_arg('magic_login_error', 'invalid', wp_login_url()));
            exit;
        }

        delete_user_meta($user_id, $this->meta_key);

        wp_set_current_user($user_id);
        wp_set_auth_cookie($user_id, false, is_ssl());
        do_action('wp_login', $user->user_login, $user);

        wp_safe_redirect($redirect_to ?: admin_url());
        exit;
    }

    private function create_and_send_link(\WP_User $user, string $redirect_to = ''): void
    {
        $token = bin2hex(random_bytes(32));
        $expires = time() + ($this->get_expiration_minutes() * MINUTE_IN_SECONDS);

        update_user_meta($user->ID, $this->meta_key, array(
            'hash' => $this->hash_token($token),
            'expires' => $expires,
            'created' => time(),
        ));

        $link = add_query_arg(array_filter(array(
            'action' => $this->verify_action,
            'uid' => $user->ID,
            'token' => $token,
            'redirect_to' => $redirect_to,
        )), wp_login_url());

        $subject = sprintf(__('[%s] Je inloglink', 'tempel-settings'), wp_specialchars_decode(get_bloginfo('name'), ENT_QUOTES));
        $message = sprintf(
            __("Hallo,\n\nGebruik deze link om in te loggen op %1\$s:\n\n%2\$s\n\nDeze link werkt eenmalig en verloopt over %3\$d minuten.\n\nHeb je dit niet aangevraagd? Dan kun je deze mail negeren.", 'tempel-settings'),
            wp_specialchars_decode(get_bloginfo('name'), ENT_QUOTES),
            esc_url_raw($link),
            $this->get_expiration_minutes()
        );

        wp_mail($user->user_email, $subject, $message);
    }

    private function is_valid_token(int $user_id, string $token): bool
    {
        if ($token === '' || strlen($token) !== 64) {
            return false;
        }

        $stored = get_user_meta($user_id, $this->meta_key, true);

        if (!is_array($stored) || empty($stored['hash']) || empty($stored['expires'])) {
            return false;
        }

        if ((int) $stored['expires'] < time()) {
            delete_user_meta($user_id, $this->meta_key);
            return false;
        }

        return hash_equals((string) $stored['hash'], $this->hash_token($token));
    }

    private function is_rate_limited(string $email): bool
    {
        $email_key = 'tempel_magic_login_email_' . md5(strtolower($email));
        $ip_key = 'tempel_magic_login_ip_' . md5($this->get_request_ip());

        if (get_transient($email_key) || get_transient($ip_key)) {
            return true;
        }

        set_transient($email_key, '1', 5 * MINUTE_IN_SECONDS);
        set_transient($ip_key, '1', MINUTE_IN_SECONDS);

        return false;
    }

    private function is_enabled(): bool
    {
        return sanitize_checkbox_value((string) return_option('tmpl_settings', 'magic_login_enabled'));
    }

    private function is_user_allowed(\WP_User $user): bool
    {
        if (user_can($user, 'manage_options')) {
            return sanitize_checkbox_value((string) return_option('tmpl_settings', 'magic_login_allow_admins'));
        }

        return true;
    }

    private function get_expiration_minutes(): int
    {
        return max(1, min(60, (int) (return_option('tmpl_settings', 'magic_login_expiration') ?: 10)));
    }

    private function hash_token(string $token): string
    {
        return hash_hmac('sha256', $token, wp_salt('auth'));
    }

    private function get_request_ip(): string
    {
        return isset($_SERVER['REMOTE_ADDR']) ? sanitize_text_field(wp_unslash($_SERVER['REMOTE_ADDR'])) : 'unknown';
    }
}
