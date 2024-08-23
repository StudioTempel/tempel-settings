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
 * Domain Path:       /languages/
 * Requires at least: 6
 * Requires PHP:      7.4
 */

namespace Tempel;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

define('TMPL_PLUGIN_PATH', plugin_dir_url(__FILE__));
define('TMPL_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('TMPL_PLUGIN_BASENAME', plugin_basename(__FILE__));
define('TMPL_PLUGIN_MAIN_FILE', __FILE__);

define('TMPL_PLUGIN_UPLOAD_PATH', plugin_dir_path(__FILE__) . 'dist/uploads/');
define('TMPL_PLUGIN_UPLOAD_URL', plugin_dir_url(__FILE__) . 'dist/uploads/');

define('TMPL_PLUGIN_DIST_URL', plugin_dir_url(__FILE__) . 'dist/');
define('TMPL_PLUGIN_CSS_URL', TMPL_PLUGIN_DIST_URL . 'css/');
define('TMPL_PLUGIN_JS_URL', TMPL_PLUGIN_DIST_URL . 'js/');
define('TMPL_PLUGIN_IMG_URL', TMPL_PLUGIN_DIST_URL . 'images/');
define('TMPL_PLUGIN_VENDOR_URL', TMPL_PLUGIN_DIST_URL . 'vendor/');
define('TMPL_PLUGIN_CACHE_URL', TMPL_PLUGIN_DIST_URL . 'cache/');
define('TMPL_PLUGIN_CACHE_PATH', plugin_dir_path(__FILE__) . 'dist/cache/');

use Tempel\Admin\TempelSettingsAdmin;
use Tempel\Public\TempelSettingsPublic;

class TempelSettings
{
    
    public $admin;
    
    public $public;
    
    public $updateChecker;
    
    static $instance;
    
    public function __construct()
    {
        register_activation_hook( __FILE__, array($this, 'setup') );
        
        $this->load_dependencies();
        $this->set_locale();
        
        
        if (is_admin()) {
            $this->admin = new TempelSettingsAdmin();
            $this->updateChecker = new TempelUpdateChecker();
        }
        
        $this->public = new TempelSettingsPublic();
    }
    
    private function load_dependencies()
    {
        require_once 'includes/TempelPluginInstaller.php';
        
        require_once 'includes/TempelPluginLocalization.php';
        
        require 'admin/TempelSettingsAdmin.php';
        
        require 'public/TempelSettingsPublic.php';
        
        require 'includes/TempelUpdateChecker.php';
    }
    
    private function set_locale()
    {
        $plugin_i18n = new TempelPluginLocalization();
        add_action('plugins_loaded', array($plugin_i18n, 'load_textdomain'));
    }
    
    private function setup()
    {
        TempelPluginInstaller::setup();
    }
    public static function get_instance()
    {
        
        if (null === self::$instance) {
            self::$instance = new self();
        }
        
        return self::$instance;
    }
}

add_action('init', function () {
    TempelSettings::get_instance();
});
