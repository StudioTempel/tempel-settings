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
 * Version:           2.0.2
 * Author:            Studio Tempel
 * Author URI:        https://studiotempel.nl
 * Text Domain:       tempel-settings
 * Domain Path:       /languages/
 * Requires at least: 6
 * Requires PHP:      7.4
 */

namespace Tempel;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

define('TMPL_PLUGIN_PATH', plugin_dir_url(__FILE__));

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

require 'admin/class-admin.php';
require 'core/class-core.php';
require 'core/class-update-checker.php';

use Tempel\Admin;
use Tempel\Core;
use Tempel\UpdateChecker;

class Tempel
{

    public $admin;

    public $core;

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
            $this->admin = new Admin\Admin();
            $this->updateChecker = new UpdateChecker\UpdateChecker();
        }

        $this->core = new Core\Core();
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
            'tmpl_branding'                     => 'on',
            'tmpl_hide_dashboard_widgets'       => 'on',
            'tmpl_disable_comments'             => 'on',
            'tmpl_disable_default_posts'        => 'on',
        );
        update_option('tempel-settings-data', $default);

        register_activation_hook(__FILE__, array("Tempel", 'setup'));
    }
    
    public function load_textdomain()
    {
        load_plugin_textdomain('tempel-settings', false, dirname(plugin_basename(__FILE__)) . '/languages/');
    }
}

//Tempel::get_instance();

add_action('init', function() {
    Tempel::get_instance();
});
