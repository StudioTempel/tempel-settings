<?php

namespace Tempel;

class Email_Login_Verification
{
    private const CODE_FIELD = 'tempel_email_login_code';
    private const CHALLENGE_FIELD = 'tempel_email_login_challenge';
    private const CHALLENGE_QUERY = 'tempel_challenge';
    private const EXPIRES_IN = 10 * MINUTE_IN_SECONDS;
    private const MAX_ATTEMPTS = 5;
    private bool $is_login_request = false;
    private string $active_challenge = '';
    private array $verified_users = array();

    public function __construct()
    {
        add_action('login_init', array($this, 'mark_login_request'), 1);
        add_action('login_form', array($this, 'render_code_field'));
        add_filter('login_body_class', array($this, 'login_body_class'));
        add_filter('authenticate', array($this, 'authenticate_challenge'), 5, 3);
        add_filter('authenticate', array($this, 'verify_authenticated_result'), 99, 3);
        add_filter('wp_authenticate_user', array($this, 'verify'), 99, 2);
    }

    public function mark_login_request(): void
    {
        $this->is_login_request = true;
    }

    public function authenticate_challenge($user, $username, $password)
    {
        if ($user instanceof \WP_User || !$this->is_wordpress_backend_login()) {
            return $user;
        }

        $token = $this->submitted_challenge();
        $challenge = $token !== '' ? get_transient($this->transient_key($token)) : false;
        if (!is_array($challenge)) {
            return $user;
        }

        $challenge_user = get_userdata(absint($challenge['user_id'] ?? 0));
        if (!($challenge_user instanceof \WP_User) || $this->has_defender_2fa($challenge_user)) {
            return $user;
        }

        $this->active_challenge = $token;
        return $challenge_user;
    }

    public function render_code_field(): void
    {
        $token = $this->current_challenge();
        if ($token === '') {
            return;
        }
        ?>
        <input type="hidden" name="<?php echo esc_attr(self::CHALLENGE_FIELD); ?>" value="<?php echo esc_attr($token); ?>">
        <p class="tempel-email-code-wrap">
            <label for="<?php echo esc_attr(self::CODE_FIELD); ?>"><?php esc_html_e('E-mailcode', 'tempel-settings'); ?></label>
            <input type="text" inputmode="numeric" autocomplete="one-time-code" pattern="[0-9]{6}" maxlength="6" class="input" name="<?php echo esc_attr(self::CODE_FIELD); ?>" id="<?php echo esc_attr(self::CODE_FIELD); ?>" value="" placeholder="<?php echo esc_attr__('E-mailcode (6 cijfers)', 'tempel-settings'); ?>" aria-label="<?php echo esc_attr__('E-mailcode', 'tempel-settings'); ?>" required autofocus>
        </p>
        <p class="tempel-email-code-reset"><a href="<?php echo esc_url(wp_login_url()); ?>"><?php esc_html_e('Opnieuw beginnen', 'tempel-settings'); ?></a></p>
        <script>document.addEventListener('DOMContentLoaded',function(){['user_login','user_pass','rememberme'].forEach(function(id){var field=document.getElementById(id);if(field){field.disabled=true;field.removeAttribute('required');}});var button=document.getElementById('wp-submit');if(button){button.value='Code controleren';}});</script>
        <?php
    }

    public function login_body_class(array $classes): array
    {
        if ($this->current_challenge() !== '') {
            $classes[] = 'tempel-email-code-step';
        }
        return $classes;
    }

    public function verify($user, $password)
    {
        if (!($user instanceof \WP_User) || !$this->is_wordpress_backend_login() || $this->has_defender_2fa($user)) {
            return $user;
        }

        if (isset($this->verified_users[$user->ID])) {
            return $user;
        }

        $token = $this->submitted_challenge();
        if ($token !== '') {
            return $this->verify_submitted_code($user, $token);
        }

        $code = (string) random_int(100000, 999999);
        $token = wp_generate_password(40, false, false);
        $challenge = array('user_id' => $user->ID, 'hash' => wp_hash_password($code), 'attempts' => 0);

        $subject = sprintf('[%s] Jouw login-code', wp_specialchars_decode(get_bloginfo('name'), ENT_QUOTES));
        $message = sprintf(
            '<p>%s</p><div style="font-size:32px;font-weight:700;letter-spacing:6px;line-height:1.4;margin:20px 0;">%s</div><p>%s</p><p>%s<br><a href="%s">%s</a><br>%s<br>%s</p>',
            esc_html__('Je login-code is:', 'tempel-settings'),
            esc_html($code),
            esc_html__('De code is 10 minuten geldig.', 'tempel-settings'),
            esc_html__('Website:', 'tempel-settings'),
            esc_url(home_url('/')),
            esc_html(home_url('/')),
            esc_html__('Tijdstip:', 'tempel-settings'),
            esc_html(wp_date('d-m-Y H:i:s'))
        );

        if (!wp_mail($user->user_email, $subject, $message, array('Content-Type: text/html; charset=UTF-8'))) {
            return new \WP_Error('tempel_email_code_mail_failed', __('De login-code kon niet worden verzonden. Neem contact op met de beheerder.', 'tempel-settings'));
        }

        set_transient($this->transient_key($token), $challenge, self::EXPIRES_IN);
        $this->active_challenge = $token;
        return $this->redirect_to_challenge($token);
    }

    public function verify_authenticated_result($user, $username, $password)
    {
        if (!($user instanceof \WP_User)) {
            return $user;
        }

        return $this->verify($user, $password);
    }

    private function verify_submitted_code(\WP_User $user, string $token)
    {
        $key = $this->transient_key($token);
        $challenge = get_transient($key);
        $submitted_code = isset($_POST[self::CODE_FIELD]) ? preg_replace('/\D+/', '', wp_unslash($_POST[self::CODE_FIELD])) : '';

        if (!is_array($challenge) || absint($challenge['user_id'] ?? 0) !== $user->ID) {
            return new \WP_Error('tempel_email_code_expired', __('De login-code is verlopen. Begin opnieuw om een nieuwe code aan te vragen.', 'tempel-settings'));
        }

        if ($submitted_code === '') {
            $this->active_challenge = $token;
            return new \WP_Error('tempel_email_code_required', '');
        }

        $attempts = absint($challenge['attempts'] ?? 0) + 1;
        if ($submitted_code !== '' && $attempts <= self::MAX_ATTEMPTS && wp_check_password($submitted_code, (string) ($challenge['hash'] ?? ''))) {
            delete_transient($key);
            $this->active_challenge = '';
            $this->verified_users[$user->ID] = true;
            return $user;
        }

        if ($attempts >= self::MAX_ATTEMPTS) {
            delete_transient($key);
            $this->active_challenge = '';
            return new \WP_Error('tempel_email_code_locked', __('Te veel onjuiste codes. Begin opnieuw om een nieuwe code te ontvangen.', 'tempel-settings'));
        }

        $challenge['attempts'] = $attempts;
        set_transient($key, $challenge, self::EXPIRES_IN);
        $this->active_challenge = $token;
        return new \WP_Error('tempel_email_code_invalid', __('De e-mailcode is onjuist. Probeer het opnieuw.', 'tempel-settings'));
    }

    private function current_challenge(): string
    {
        $token = $this->active_challenge ?: $this->submitted_challenge();
        return $token !== '' && is_array(get_transient($this->transient_key($token))) ? $token : '';
    }

    private function submitted_challenge(): string
    {
        if (isset($_POST[self::CHALLENGE_FIELD])) {
            return sanitize_text_field(wp_unslash($_POST[self::CHALLENGE_FIELD]));
        }

        return isset($_GET[self::CHALLENGE_QUERY]) ? sanitize_text_field(wp_unslash($_GET[self::CHALLENGE_QUERY])) : '';
    }

    private function redirect_to_challenge(string $token)
    {
        $args = array(self::CHALLENGE_QUERY => $token);
        if (!empty($_REQUEST['redirect_to'])) {
            $args['redirect_to'] = esc_url_raw(wp_unslash($_REQUEST['redirect_to']));
        }
        $url = add_query_arg($args, wp_login_url());

        if (defined('TEMPEL_SETTINGS_TESTING') && TEMPEL_SETTINGS_TESTING) {
            $GLOBALS['tempel_test_redirect'] = $url;
            return new \WP_Error('tempel_email_code_redirect', __('Doorsturen naar de e-mailcode.', 'tempel-settings'));
        }

        wp_safe_redirect($url);
        exit;
    }

    private function has_defender_2fa(\WP_User $user): bool
    {
        $configured = false;

        if (
            function_exists('wd_di') &&
            class_exists('WP_Defender\\Model\\Setting\\Two_Fa') &&
            class_exists('WP_Defender\\Component\\Two_Fa')
        ) {
            try {
                $settings = \wd_di()->get(\WP_Defender\Model\Setting\Two_Fa::class);
                $component = \wd_di()->get(\WP_Defender\Component\Two_Fa::class);

                if (
                    is_object($settings) &&
                    method_exists($settings, 'is_active') &&
                    $settings->is_active() &&
                    is_object($component) &&
                    method_exists($component, 'get_available_providers_for_user')
                ) {
                    $configured = $component->get_available_providers_for_user($user) !== array();
                }
            } catch (\Throwable $exception) {
                $configured = false;
            }
        }

        return (bool) apply_filters('tempel_defender_2fa_configured', $configured, $user->ID);
    }

    private function is_wordpress_backend_login(): bool
    {
        $script = isset($_SERVER['SCRIPT_NAME']) ? basename(sanitize_text_field(wp_unslash($_SERVER['SCRIPT_NAME']))) : '';
        return ($this->is_login_request || $script === 'wp-login.php') && empty($_REQUEST['woocommerce-login-nonce']);
    }

    private function transient_key(string $token): string
    {
        return 'tempel_login_' . hash('sha256', $token);
    }
}
