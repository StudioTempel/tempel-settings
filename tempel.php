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
 * Version:           2.8.0
 * Author:            Studio Tempel
 * Author URI:        https://studiotempel.nl
 * Text Domain:       tempel-settings
 * Domain Path:       /languages/
 * Requires at least: 6
 * Requires PHP:      8.0
 */

namespace Tempel;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

if ( ! defined('TEMPEL_SETTINGS_VERSION') ) define('TEMPEL_SETTINGS_VERSION', '2.8.0');
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
        $this->apply_security_lock_default();
        $this->deactivate_vulnerable_wpmudev_dashboard();
        Status_Log::init();
        Status_Monitor::init();

        if (is_admin() && version_compare((string) get_option('tempel_settings_plugin_replacements_version'), '2.7.25', '<')) {
            add_action('admin_init', array($this, 'deactivate_replaced_plugins'));
        }
        
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
        require_once TEMPEL_SETTINGS_DIR . 'src/includes/status-log.php';
        require_once TEMPEL_SETTINGS_DIR . 'src/includes/status-monitor.php';
    }
    
    private function set_locale()
    {
        $plugin_i18n = new Localization();
        add_action('plugins_loaded', array($plugin_i18n, 'load_plugin_textdomain'));
    }

    private function apply_update_defaults(): void
    {
        $defaults_version = get_option('tempel_settings_defaults_version');

        if (version_compare((string) $defaults_version, '2.7.25', '>=')) {
            return;
        }

        $settings = get_option('tmpl_settings', array());

        if (!is_array($settings)) {
            $settings = array();
        }

        if (version_compare((string) $defaults_version, '2.6.3', '<')) {
            $settings['gf_bag_address_enabled'] = '';
        }

        $settings['skip_bundled_themes'] = 'on';
        $settings['duplicate_content'] = 'on';
        $settings['user_switching'] = 'on';
        update_option('tmpl_settings', $settings);

        update_option('tempel_settings_defaults_version', '2.7.25');
    }

    private function apply_security_lock_default(): void
    {
        if (get_option('tempel_settings_security_lock_default_applied')) {
            return;
        }

        $settings = get_option('tmpl_settings', array());
        $settings = is_array($settings) ? $settings : array();
        $settings['security_lock'] = 'on';
        update_option('tmpl_settings', $settings);
        update_option('tempel_settings_security_lock_default_applied', true);
    }

    private function deactivate_vulnerable_wpmudev_dashboard(): void
    {
        $plugin = 'wpmudev-updates/update-notifications.php';
        $active_plugins = (array) get_option('active_plugins', array());
        $network_plugins = is_multisite() ? (array) get_site_option('active_sitewide_plugins', array()) : array();
        $network_active = isset($network_plugins[$plugin]);

        if (!in_array($plugin, $active_plugins, true) && !$network_active) {
            return;
        }

        $plugin_file = WP_PLUGIN_DIR . '/' . $plugin;
        if (!is_readable($plugin_file)) {
            return;
        }

        $data = get_file_data($plugin_file, array('Version' => 'Version'));
        $version = (string) ($data['Version'] ?? '');

        if ($version === '' || version_compare($version, '5.0.1', '>')) {
            return;
        }

        if (!function_exists('deactivate_plugins')) {
            require_once ABSPATH . 'wp-admin/includes/plugin.php';
        }
        deactivate_plugins($plugin, false, $network_active);

        if ($network_active) {
            update_site_option('tempel_settings_wpmudev_deactivated_version', $version);
        } else {
            update_option('tempel_settings_wpmudev_deactivated_version', $version);
        }
    }

    public function deactivate_replaced_plugins(): void
    {
        if (!current_user_can('activate_plugins')) {
            return;
        }

        require_once ABSPATH . 'wp-admin/includes/plugin.php';

        $replaced_plugins = array(
            'duplicate-post/duplicate-post.php',
            'user-switching/user-switching.php',
        );

        foreach ($replaced_plugins as $plugin) {
            if (is_multisite() && is_plugin_active_for_network($plugin)) {
                if (!current_user_can('manage_network_plugins')) {
                    return;
                }

                deactivate_plugins($plugin, false, true);
                continue;
            }

            if (is_plugin_active($plugin)) {
                deactivate_plugins($plugin, false, false);
            }
        }

        update_option('tempel_settings_plugin_replacements_version', '2.7.25');
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
