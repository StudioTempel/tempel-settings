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
 * Version:           2.7.8
 * Author:            Studio Tempel
 * Author URI:        https://studiotempel.nl
 * Text Domain:       tempel-settings
 * Domain Path:       /languages/
 * Requires at least: 6
 * Requires PHP:      8.0
 */

namespace Tempel;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

if ( ! defined('TEMPEL_SETTINGS_VERSION') ) define('TEMPEL_SETTINGS_VERSION', '2.7.8');
if ( ! defined('TEMPEL_SETTINGS_FILE') ) define('TEMPEL_SETTINGS_FILE', __FILE__);
if ( ! defined('TEMPEL_SETTINGS_BASENAME') ) define('TEMPEL_SETTINGS_BASENAME', plugin_basename(__FILE__));
if ( ! defined('TEMPEL_SETTINGS_DIR') ) define('TEMPEL_SETTINGS_DIR', plugin_dir_path(__FILE__));
if ( ! defined('TEMPEL_SETTINGS_URL') ) define('TEMPEL_SETTINGS_URL', plugin_dir_url(__FILE__));

if ( ! defined ('TEMPEL_SETTINGS_ASSET_URL') ) define('TEMPEL_SETTINGS_ASSET_URL', plugin_dir_url(__FILE__) . 'dist/');
if ( ! defined('TEMPEL_SETTINGS_ASSET_DIR') ) define('TEMPEL_SETTINGS_ASSET_DIR', plugin_dir_path(__FILE__) . 'dist/');
if( ! defined('TEMPEL_SETTINGS_LANG_DIR') ) define('TEMPEL_SETTINGS_LANG_DIR', dirname(plugin_basename(__FILE__)) . '/languages');

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

        if (version_compare((string) $defaults_version, '2.6.3', '>=')) {
            return;
        }

        $settings = get_option('tmpl_settings', array());

        if (is_array($settings)) {
            $settings['gf_bag_address_enabled'] = '';
            update_option('tmpl_settings', $settings);
        }

        update_option('tempel_settings_defaults_version', '2.6.3');
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
