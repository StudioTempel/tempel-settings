<?php
// Standalone regression checks: php tools/test-retention-and-notifications.php
namespace {
    if (PHP_SAPI !== 'cli') exit;

    define('DAY_IN_SECONDS', 86400);
    define('HOUR_IN_SECONDS', 3600);
    define('MINUTE_IN_SECONDS', 60);
    define('TEMPEL_SETTINGS_DIR', dirname(__DIR__) . '/');
    define('TEMPEL_SETTINGS_TESTING', true);
    $GLOBALS['options'] = array();
    $GLOBALS['scheduled'] = array();
    $GLOBALS['mail'] = array();
    $GLOBALS['transients'] = array();
    $GLOBALS['user_meta'] = array();
    $GLOBALS['users'] = array();

    function add_action(...$args) {}
    function add_filter(...$args) {}
    function apply_filters($tag, $value, ...$args) { return $tag === 'tempel_defender_2fa_configured' && array_key_exists('defender_2fa_configured', $GLOBALS) ? (bool) $GLOBALS['defender_2fa_configured'] : $value; }
    function __($text, $domain = null) { return $text; }
    function esc_html($text) { return htmlspecialchars((string) $text, ENT_QUOTES); }
    function esc_html__($text, $domain = null) { return esc_html($text); }
    function esc_attr($text) { return htmlspecialchars((string) $text, ENT_QUOTES); }
    function esc_attr__($text, $domain = null) { return esc_attr($text); }
    function esc_url($text) { return esc_attr($text); }
    function esc_url_raw($text) { return (string) $text; }
    function esc_html_e($text, $domain = null) { echo htmlspecialchars((string) $text, ENT_QUOTES); }
    function get_option($key, $default = false) { return $GLOBALS['options'][$key] ?? $default; }
    function update_option($key, $value, $autoload = null) { $GLOBALS['options'][$key] = $value; return true; }
    function absint($value) { return abs((int) $value); }
    function sanitize_text_field($value) { return trim(strip_tags((string) $value)); }
    function wp_specialchars_decode($value, $flags = ENT_QUOTES) { return html_entity_decode($value, $flags); }
    function get_bloginfo($key) { return 'Testsite'; }
    function home_url($path = '') { return 'https://example.test' . $path; }
    function admin_url($path = '') { return 'https://example.test/wp-admin/' . ltrim($path, '/'); }
    function wp_get_current_user() { return new class { public $user_login = 'beheerder'; public $ID = 7; public function exists() { return true; } }; }
    function wp_date($format, $timestamp = null, $timezone = null) { return gmdate($format, $timestamp ?? time()); }
    function current_time($type, $gmt = false) { return 1700000000; }
    function wp_mail($to, $subject, $message, $headers = array()) { $GLOBALS['mail'][] = compact('to', 'subject', 'message', 'headers'); return true; }
    function wp_unslash($value) { return $value; }
    function wp_hash_password($value) { return password_hash($value, PASSWORD_DEFAULT); }
    function wp_hash($value, $scheme = 'auth') { return hash_hmac('sha256', $value, 'test-' . $scheme); }
    function wp_check_password($value, $hash) { return password_verify($value, $hash); }
    function wp_generate_password($length = 12, $special = true, $extra = false) { return substr(str_repeat('ChallengeToken', 4), 0, $length); }
    function wp_login_url() { return 'https://example.test/wp-login.php'; }
    function add_query_arg($args, $url) { return $url . '?' . http_build_query($args); }
    function get_transient($key) { return $GLOBALS['transients'][$key] ?? false; }
    function set_transient($key, $value, $expiration) { $GLOBALS['transients'][$key] = $value; return true; }
    function delete_transient($key) { unset($GLOBALS['transients'][$key]); return true; }
    function get_user_meta($id, $key, $single = false) { return $GLOBALS['user_meta'][$id][$key] ?? ''; }
    function get_userdata($id) { return $GLOBALS['users'][$id] ?? false; }
    function wp_next_scheduled($hook) { return $GLOBALS['scheduled'][$hook] ?? false; }
    function wp_schedule_event($timestamp, $recurrence, $hook) { $GLOBALS['scheduled'][$hook] = $timestamp; return true; }
    function wp_clear_scheduled_hook($hook) { unset($GLOBALS['scheduled'][$hook]); return 1; }
    function is_wp_error($value) { return false; }

    class WP_User {
        public $ID;
        public $user_email;
        public $user_login;
        public $roles;
        public function __construct($id, $email, $login = 'gebruiker', $roles = array()) { $this->ID = $id; $this->user_email = $email; $this->user_login = $login; $this->roles = $roles; }
    }
    class WP_Error {
        public $code;
        public function __construct($code = '', $message = '') { $this->code = $code; }
    }

    class GFAPI {
        public static $entries = array();
        public static $queries = array();
        public static $deleted = array();
        public static $forms = array();
        public static $counts = array();
        public static function get_entries($forms, $criteria, $sorting, $paging) {
            self::$queries[] = compact('forms', 'criteria', 'sorting', 'paging');
            return array_slice(self::$entries[$criteria['status']] ?? array(), 0, $paging['page_size']);
        }
        public static function delete_entry($id) { self::$deleted[] = $id; return true; }
        public static function get_form($id) { return self::$forms[$id] ?? false; }
        public static function count_entries($id, $criteria) { self::$queries[] = compact('id', 'criteria'); return self::$counts[$id] ?? 0; }
    }

    function check($condition, $label) {
        if (!$condition) throw new \RuntimeException($label);
        echo "PASS: $label\n";
    }
}

namespace Tempel {
    require dirname(__DIR__) . '/src/includes/helper-functions.php';
    require dirname(__DIR__) . '/src/includes/settings/plugin-install-notifier.php';
    require dirname(__DIR__) . '/src/includes/settings/form-entry-retention.php';
    require dirname(__DIR__) . '/src/includes/settings/gf-simple-antispam.php';
    require dirname(__DIR__) . '/src/includes/settings/email-login-verification.php';
    require dirname(__DIR__) . '/src/includes/widget-conversion-helper-functions.php';

    $notifier = new Plugin_Install_Notifier();
    $upgrader = new class { public function plugin_info() { return 'voorbeeld/voorbeeld.php'; } };
    $notifier->notify($upgrader, array('type' => 'plugin', 'action' => 'update'));
    \check(count($GLOBALS['mail']) === 0, 'Plugin update sends no installation email');
    $notifier->notify($upgrader, array('type' => 'plugin', 'action' => 'install'));
    \check(count($GLOBALS['mail']) === 1, 'Plugin installation sends one email');
    \check($GLOBALS['mail'][0]['to'] === 'info@studiotempel.nl', 'Notification uses fixed Studio Tempel recipient');
    \check(str_contains($GLOBALS['mail'][0]['message'], 'voorbeeld/voorbeeld.php'), 'Notification identifies installed plugin');
    \check(str_contains($GLOBALS['mail'][0]['message'], 'beheerder (ID 7)'), 'Notification identifies current user');

    $GLOBALS['users'][20] = new \WP_User(20, 'nieuw@example.test', 'nieuwebeheerder', array('administrator'));
    $notifier->queue_role_change(20, 'administrator', array());
    $notifier->queue_new_user(20);
    $notifier->send_administrator_notifications();
    \check(count($GLOBALS['mail']) === 2, 'New administrator sends one deduplicated notification');
    \check(str_contains($GLOBALS['mail'][1]['subject'], 'Nieuwe beheerder aangemaakt'), 'New administrator notification has correct action');
    $GLOBALS['users'][21] = new \WP_User(21, 'bestaand@example.test', 'bestaandegebruiker', array('administrator'));
    $notifier->queue_role_change(21, 'administrator', array('editor'));
    $notifier->send_administrator_notifications();
    \check(count($GLOBALS['mail']) === 3, 'Administrator promotion sends one notification');
    \check(str_contains($GLOBALS['mail'][2]['subject'], 'gepromoveerd naar beheerder'), 'Promotion notification has correct action');

    $retention = new Form_Entry_Retention();
    $GLOBALS['options']['tmpl_settings'] = array('form_entry_retention_enabled' => '', 'form_entry_retention_days' => '0');
    $GLOBALS['scheduled'][Form_Entry_Retention::CRON_HOOK] = 1;
    $retention->schedule();
    \check(!isset($GLOBALS['scheduled'][Form_Entry_Retention::CRON_HOOK]), 'Disabled retention clears scheduled cleanup');
    \check(Form_Entry_Retention::get_days() === 365, 'Empty or zero retention period uses safe default');

    $GLOBALS['options']['tmpl_settings'] = array('form_entry_retention_enabled' => 'on', 'form_entry_retention_days' => '14');
    $retention->schedule();
    \check(isset($GLOBALS['scheduled'][Form_Entry_Retention::CRON_HOOK]), 'Enabled retention schedules hourly cleanup');
    \GFAPI::$entries = array(
        'active' => array_map(fn($id) => array('id' => $id), range(1, 499)),
        'spam' => array(array('id' => 500), array('id' => 501)),
        'trash' => array(array('id' => 502)),
    );
    $deleted_count = $retention->cleanup();
    \check($deleted_count === 500, 'Cleanup reports the number of permanently deleted entries');
    \check(count(\GFAPI::$deleted) === 500, 'Cleanup permanently deletes at most 500 entries per run');
    \check(count(\GFAPI::$queries) === 2, 'Cleanup stops querying after batch is full');
    \check(\GFAPI::$queries[0]['criteria']['status'] === 'active', 'Cleanup includes active entries');
    \check(\GFAPI::$queries[1]['criteria']['status'] === 'spam', 'Cleanup includes spam entries');
    \check(\GFAPI::$queries[0]['criteria']['end_date'] === '2023-10-31 22:13:19', 'Cleanup keeps entries exactly on the configured retention boundary');

    $GLOBALS['options']['tmpl_settings'] = array('form_entry_retention_enabled' => '', 'form_entry_retention_days' => '7');
    \check(get_conversion_period_days() === 30, 'Conversion dashboard stays at 30 days when retention is disabled');
    $GLOBALS['options']['tmpl_settings']['form_entry_retention_enabled'] = 'on';
    \check(get_conversion_period_days() === 7, 'Conversion dashboard follows retention below 30 days');
    $GLOBALS['options']['tmpl_settings']['form_entry_retention_days'] = '90';
    \check(get_conversion_period_days() === 30, 'Conversion dashboard never exceeds 30 days');

    $GLOBALS['options']['tmpl_settings']['form_entry_retention_days'] = '14';
    $GLOBALS['options']['tmpl_widget_settings'] = array('conversion_selected_forms' => array('6', '7'));
    \GFAPI::$forms = array(6 => array('title' => 'Contact'), 7 => array('title' => 'Offerte'));
    \GFAPI::$counts = array(6 => 12, 7 => 3);
    \GFAPI::$queries = array();
    $conversion_items = get_form_submissions_by_id();
    \check(count($conversion_items) === 2, 'Conversion dashboard returns one result item per selected form');
    \check($conversion_items[0]['submissions'] === 12 && $conversion_items[1]['submissions'] === 3, 'Conversion counts remain separate from the result list');
    \check($conversion_items[0]['link'] === 'https://example.test/wp-admin/admin.php?page=gf_entries&view=entries&id=6', 'Conversion item links to the matching Gravity Forms entries');

    $GLOBALS['options']['tmpl_settings']['gf_antispam_min_seconds'] = '3';
    $antispam = new GF_Simple_Antispam();
    \check(str_contains(GF_Simple_Antispam::honeypot_css(), '.gform_validation_container') && str_contains(GF_Simple_Antispam::honeypot_css(), 'display:none!important'), 'Antispam always hides the Gravity Forms honeypot');
    $honeypot_form = $antispam->enable_honeypot(array('id' => 2));
    \check(!empty($honeypot_form['enableHoneypot']) && $honeypot_form['honeypotAction'] === 'spam', 'Global antispam enables the Gravity Forms honeypot');
    $tag = $antispam->add_invisible_fields('<form>', array('id' => 2));
    \check(str_contains($tag, 'tempel_gf_started') && str_contains($tag, 'tempel_gf_js'), 'Antispam adds invisible timing and JavaScript fields');
    $_POST = array('gform_submit' => '2', 'tempel_gf_started' => time() - 5, 'tempel_gf_js' => '1');
    $_POST['tempel_gf_proof'] = wp_hash('2|' . $_POST['tempel_gf_started'], 'nonce');
    \check($antispam->is_spam(false, array('id' => 2), array()) === false, 'Normal JavaScript submission after the minimum time is accepted');
    $_POST['tempel_gf_js'] = '';
    \check($antispam->is_spam(false, array('id' => 2), array()) === true, 'Submission without JavaScript proof is marked as spam');
    $_POST = array();

    $GLOBALS['mail'] = array();
    $_SERVER['SCRIPT_NAME'] = '/wp-login.php';
    $_REQUEST = $_POST = array();
    $login_verification = new Email_Login_Verification();
    $login_user = new \WP_User(12, 'user@example.test');
    $GLOBALS['users'][12] = $login_user;
    ob_start();
    $login_verification->render_code_field();
    \check(ob_get_clean() === '', 'Initial login screen does not show a code field');
    $alternate_auth = new Email_Login_Verification();
    $alternate_result = $alternate_auth->verify_authenticated_result($login_user, 'studiotempel', 'correct-password');
    \check($alternate_result instanceof \WP_Error && $alternate_result->code === 'tempel_email_code_redirect', 'A user returned by an alternate authentication provider still requires an email code');
    $GLOBALS['mail'] = array();
    $GLOBALS['transients'] = array();
    $GLOBALS['tempel_test_redirect'] = '';
    $first_login = $login_verification->verify($login_user, 'correct-password');
    \check($first_login instanceof \WP_Error && $first_login->code === 'tempel_email_code_redirect', 'Backend login redirects to dedicated email-code step');
    \check(count($GLOBALS['mail']) === 1 && $GLOBALS['mail'][0]['to'] === 'user@example.test', 'Login code is emailed to the user');
    \check(str_contains($GLOBALS['tempel_test_redirect'] ?? '', 'tempel_challenge='), 'First login step redirects without returning an authentication failure');
    preg_match('/letter-spacing:6px[^>]*>(\d{6})<\/div>/', $GLOBALS['mail'][0]['message'], $code_match);
    \check(str_contains($GLOBALS['mail'][0]['message'], 'font-size:32px'), 'Login code is displayed prominently on its own line in the email');
    \check(in_array('Content-Type: text/html; charset=UTF-8', $GLOBALS['mail'][0]['headers'], true), 'Login code email is sent as HTML');
    ob_start();
    $login_verification->render_code_field();
    $code_step = ob_get_clean();
    preg_match('/name="tempel_email_login_challenge" value="([^"]+)"/', $code_step, $challenge_match);
    \check(str_contains($code_step, 'Opnieuw beginnen'), 'Code screen provides a reset link');
    \check(str_contains($code_step, 'field.disabled=true'), 'Code screen disables hidden required credential fields');
    $_GET['tempel_challenge'] = $challenge_match[1] ?? '';
    $redirected_code_step = new Email_Login_Verification();
    $redirected_code_step->mark_login_request();
    \check(in_array('tempel-email-code-step', $redirected_code_step->login_body_class(array()), true), 'Redirected GET request receives dedicated code-screen class');
    unset($_GET['tempel_challenge']);
    $_POST['tempel_email_login_code'] = $code_match[1] ?? '';
    $_POST['tempel_email_login_challenge'] = $challenge_match[1] ?? '';
    $challenge_user = $login_verification->authenticate_challenge(null, '', '');
    \check($challenge_user === $login_user, 'Temporary challenge resumes authenticated user without storing password');
    $_POST['tempel_email_login_code'] = '';
    $empty_code_result = $login_verification->verify($challenge_user, '');
    \check($empty_code_result instanceof \WP_Error && $empty_code_result->code === 'tempel_email_code_required', 'Empty code submission stays silently on the code step');
    $_POST['tempel_email_login_code'] = $code_match[1] ?? '';
    \check($login_verification->verify($challenge_user, '') === $login_user, 'Correct six-digit code completes login');
    $login_verification = new Email_Login_Verification();
    $GLOBALS['defender_2fa_configured'] = true;
    $_POST = array();
    \check($login_verification->verify($login_user, 'correct-password') === $login_user, 'Configured Defender provider bypasses fallback code');
    unset($GLOBALS['defender_2fa_configured']);
    $GLOBALS['user_meta'][12]['wd_2fa_enabled_providers'] = array('stale-totp');
    $_POST = array();
    $stale_meta_result = $login_verification->verify($login_user, 'correct-password');
    \check($stale_meta_result instanceof \WP_Error && $stale_meta_result->code === 'tempel_email_code_redirect', 'Stale Defender metadata cannot bypass Tempel verification');
    unset($GLOBALS['user_meta'][12]);
    $_REQUEST['woocommerce-login-nonce'] = 'shop-login';
    \check($login_verification->verify($login_user, 'correct-password') === $login_user, 'WooCommerce frontend login is excluded');

    $GLOBALS['mail'] = array();
    $GLOBALS['transients'] = array();
    $GLOBALS['user_meta'] = array();
    $_REQUEST = $_POST = array();
    $_SERVER['SCRIPT_NAME'] = '/index.php';
    $masked_login_verification = new Email_Login_Verification();
    $masked_login_verification->mark_login_request();
    $masked_result = $masked_login_verification->verify($login_user, 'correct-password');
    \check($masked_result instanceof \WP_Error && $masked_result->code === 'tempel_email_code_redirect', 'Masked Defender login route redirects to email code');
    \check(count($GLOBALS['mail']) === 1, 'Masked login route sends one email code');
    ob_start();
    $masked_login_verification->render_code_field();
    $rendered_code_field = ob_get_clean();
    \check(strpos($rendered_code_field, '</label>') < strpos($rendered_code_field, 'id="tempel_email_login_code"'), 'Code input is outside hidden branded login label');
    \check(str_contains($rendered_code_field, 'placeholder="E-mailcode (6 cijfers)"'), 'Code field has a visible placeholder');
}
