<?php

/**
 * StudioTempel settings
 *
 * @package           tempel-settings
 * @link              https://github.com/StudioTempel/tempel-settings
 * @author            Studio Tempel & Job Ligthart
 * @copyright         2024 Studio Tempel
 * @license           GPL v2 or later
 *
 * Plugin Name:       Tempel settings
 * Description:       Plugin that compliments custom-built themes produced by Studio Tempel
 * Version:           2.0.9
 * Author:            Studio Tempel
 * Author URI:        https://studiotempel.nl
 * Text Domain:       tempel-settings
 * Domain Path:       /lang/
 * Requires at least: 6
 * Requires PHP:      7.4
 */

namespace Tempel;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

define('TMPL_PLUGIN_PATH', plugin_dir_url(__FILE__));
define('TMPL_PLUGIN_DIR', plugin_dir_path(__FILE__));

define('TMPL_PLUGIN_UPLOAD_PATH', plugin_dir_path(__FILE__) . 'dist/uploads/');
define('TMPL_PLUGIN_UPLOAD_URL', plugin_dir_url(__FILE__) . 'dist/uploads/');

define('TMPL_PLUGIN_DIST_URL', plugin_dir_url(__FILE__) . 'dist/');
define('TMPL_PLUGIN_CSS_URL', TMPL_PLUGIN_DIST_URL . 'css/');
define('TMPL_PLUGIN_JS_URL', TMPL_PLUGIN_DIST_URL . 'js/');
define('TMPL_PLUGIN_IMG_URL', TMPL_PLUGIN_DIST_URL . 'images/');
define('TMPL_PLUGIN_VENDOR_URL', TMPL_PLUGIN_DIST_URL . 'vendor/');
define('TMPL_PLUGIN_CACHE_URL', TMPL_PLUGIN_DIST_URL . 'cache/');
define('TMPL_PLUGIN_CACHE_PATH', plugin_dir_path(__FILE__) . 'dist/cache/');


// Defaults
define('TMPL_DEFAULT_FAQ_LINK', 'https://studiotempel.nl/veelgestelde-vragen');
define('TMPL_DEFAULT_CONTACT_LINK', 'https://studiotempel.nl/contact');
define('TMPL_DEFAULT_LOGIN_SCREEN_IMAGE', TMPL_PLUGIN_IMG_URL . 'login-screen-bg,webp');

require 'admin/TempelSettingsAdmin.php';
require 'public/TempelSettingsPublic.php';
require 'includes/TempelUpdateChecker.php';

use Tempel\Admin\TempelSettingsAdmin;
use Tempel\Public\TempelSettingsPublic;

class Tempel
{

    public $admin;

    public $public;

    public $updateChecker;

    static $instance;
    /**
     * Tempel Constructor
     */
    public function __construct()
    {
        $this->init();
        add_action('init', array($this, 'load_textdomain'));


        if (is_admin()) {
            $this->admin = new TempelSettingsAdmin();
            $this->updateChecker = new TempelUpdateChecker();
        }

        $this->public = new TempelSettingsPublic();
    }

    public static function get_instance()
    {

        if (null === self::$instance) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    private function init()
    {
        register_activation_hook(__FILE__, array($this, 'install'));
    }

    public static function install()
    {
        $default = array(
            'enable_branding'                   => 'on',
            'disable_comments'                  => 'on',
            'disable_default_pt'                => 'off',
            'hide_dashboard_widgets'            => 'on',
            'enable_widgets'                    => 'off',
            'svg_support'                       => 'of',
        );
        update_option('tmpl_settings', $default);
        
        $widget_defaults = array(
            'conversion_selected_forms'                 => '',
            'status_safeupdate_day'                     => 'monday',
            'status_backup_interval'                    => '12:30',
            'status_last_checkup_date'                  => '06/2024',
            'status_show_service_contract_tier'         => 'on',
            'status_service_contract_upgradable'        => 'on',
            'status_service_contract_tier'              => 'Plus',
            'status_service_contract_upgrade_link'      => 'https://studiotempel.nl/servicecontract',
            'support_faq_link'                          => TMPL_DEFAULT_FAQ_LINK,
            'support_ticket_link'                       => TMPL_DEFAULT_CONTACT_LINK,
        );
        update_option('tmpl_widget_settings', $widget_defaults);

        register_activation_hook(__FILE__, array("Tempel", 'setup'));
    }
    
    public function load_textdomain()
    {
        load_plugin_textdomain('tempel-settings', false, dirname(plugin_basename(__FILE__)) . '/lang/');
    }
}

add_action('init', function() {
    Tempel::get_instance();
});
