<?php

/**
 * StudioTempel settings
 *
 * @package           tempel-settings
 * @link              https://github.com/StudioTempel/tempel-settings
 * @author            Studio Tempel & Job Ligthart
 * @copyright         20245 Studio Tempel
 * @license           GPL v2 or later
 *
 * Plugin Name:       Tempel settings
 * Description:       Plugin that compliments custom-built themes produced by Studio Tempel
 * Version:           2.7.23
 * Author:            Studio Tempel
 * Author URI:        https://studiotempel.nl
 * Text Domain:       tempel-settings
 * Domain Path:       /languages/
 * Requires at least: 6
 * Requires PHP:      8.0
 */

namespace Tempel;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

if ( ! defined('TEMPEL_SETTINGS_VERSION') ) define('TEMPEL_SETTINGS_VERSION', '2.7.23');
if ( ! defined('TEMPEL_SETTINGS_FILE') ) define('TEMPEL_SETTINGS_FILE', __FILE__);
if ( ! defined('TEMPEL_SETTINGS_BASENAME') ) define('TEMPEL_SETTINGS_BASENAME', plugin_basename(__FILE__));
if ( ! defined('TEMPEL_SETTINGS_DIR') ) define('TEMPEL_SETTINGS_DIR', plugin_dir_path(__FILE__));
if ( ! defined('TEMPEL_SETTINGS_URL') ) define('TEMPEL_SETTINGS_URL', plugin_dir_url(__FILE__));

if ( ! defined ('TEMPEL_SETTINGS_ASSET_URL') ) define('TEMPEL_SETTINGS_ASSET_URL', plugin_dir_url(__FILE__) . 'dist/');
if ( ! defined('TEMPEL_SETTINGS_ASSET_DIR') ) define('TEMPEL_SETTINGS_ASSET_DIR', plugin_dir_path(__FILE__) . 'dist/');
if( ! defined('TEMPEL_SETTINGS_LANG_DIR') ) define('TEMPEL_SETTINGS_LANG_DIR', dirname(plugin_basename(__FILE__)) . '/languages');

add_filter('gettext', __NAMESPACE__ . '\filter_security_login_message', 20, 3);
add_filter('wp_die_handler', __NAMESPACE__ . '\filter_security_login_die_handler', 20);

function get_security_login_message(): string
{
    return "Om veiligheidsredenen hebben wij de standaard WordPress admin URL aangepast. Hierover is een mail verzonden met onderwerp: Belangrijk: nieuwe WordPress admin-URL\n\nTeam StudioTempel";
}

function filter_security_login_message($translation, $text, $domain)
{
    if ($text === 'This feature is temporarily forbidden for security reasons. Try logging in again.') {
        return get_security_login_message();
    }

    return $translation;
}

function filter_security_login_die_handler($handler)
{
    if ($handler !== __NAMESPACE__ . '\handle_security_login_wp_die') {
        $GLOBALS['tempel_settings_wp_die_handler'] = $handler;
    }

    return __NAMESPACE__ . '\handle_security_login_wp_die';
}

function handle_security_login_wp_die($message, $title = '', $args = array()): void
{
    if (is_string($message) && tempel_is_security_login_message($message)) {
        _default_wp_die_handler(
            '<p>' . nl2br(esc_html(get_security_login_message())) . '</p>',
            $title,
            $args
        );

        return;
    }

    $handler = $GLOBALS['tempel_settings_wp_die_handler'] ?? '_default_wp_die_handler';

    if ($handler === __NAMESPACE__ . '\handle_security_login_wp_die') {
        $handler = '_default_wp_die_handler';
    }

    call_user_func($handler, $message, $title, $args);
}

function tempel_is_security_login_message(string $message): bool
{
    $plain_message = html_entity_decode(wp_strip_all_tags($message), ENT_QUOTES, get_bloginfo('charset') ?: 'UTF-8');

    return strpos($plain_message, 'Om veiligheidsredenen hebben wij de standaard WordPress admin URL aangepast.') !== false
        || strpos($plain_message, 'This feature is temporarily forbidden for security reasons. Try logging in again.') !== false;
}

class TempelSettings
{
    static $instance;
    
    public function __construct()
    {

        $this->load_dependencies();

        $this->set_locale();
        $this->apply_update_defaults();
        
        if (is_admin()) {
            new Admin();
            new Updater();
        }
        
        
        Settings::load_settings();
    }
    

    
    private function load_dependencies()
    {
        require_once TEMPEL_SETTINGS_DIR . 'includes/localization.php';
        require_once TEMPEL_SETTINGS_DIR . 'src/admin.php';
        require_once TEMPEL_SETTINGS_DIR . 'src/settings.php';
        require_once TEMPEL_SETTINGS_DIR . 'includes/updater.php';
    }
    
    private function set_locale()
    {
        $plugin_i18n = new Localization();
        add_action('plugins_loaded', array($plugin_i18n, 'load_plugin_textdomain'));
    }

    private function apply_update_defaults(): void
    {
        $defaults_version = get_option('tempel_settings_defaults_version');

        if (version_compare((string) $defaults_version, '2.7.16', '>=')) {
            return;
        }

        $settings = get_option('tmpl_settings', array());

        if (is_array($settings)) {
            if (version_compare((string) $defaults_version, '2.6.3', '<')) {
                $settings['gf_bag_address_enabled'] = '';
            }

            $settings['skip_bundled_themes'] = 'on';
            update_option('tmpl_settings', $settings);
        }

        update_option('tempel_settings_defaults_version', '2.7.16');
    }

    public static function get_instance()
    {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        
        return self::$instance;
    }
    
    static function activate()
    {
        require_once TEMPEL_SETTINGS_DIR . 'includes/activator.php';
        Activator::activate();
    }
}

register_activation_hook(__FILE__, ['Tempel\TempelSettings', 'activate']);

add_action('init', function () {
    TempelSettings::get_instance();
});
