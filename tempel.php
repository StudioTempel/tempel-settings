<?php

/**
 * StudioTempel settings
 *
 * @package           tempel-settings
 * @author            Studio Tempel
 * @copyright         2023 Studio Tempel
 * @license           GPL-2.0-or-later
 *
 * @wordpress-plugin
 * Plugin Name:       Tempel settings
 * Plugin URI:        https://studiotempel.nl/tempel-settings
 * Description:       Simple plugin with settings for login branding, admin branding, disabling comments and more.
 * Version:           1.6
 *
 * Requires at least: 6
 * Requires PHP:      8
 * Author:            Studio Tempel
 * Author URI:        https://studiotempel.nl
 * Text Domain:       tempel-settings
 * License:           GPL v2 or later
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Update URI:        https://studiotempel.nl/tempel-settings
 */

namespace Tempel;

if(!defined('ABSPATH')) exit; // Exit if accessed directly

define('TMPL_PLUGIN_PATH', plugin_dir_url(__FILE__));

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
        $this->admin = new Admin\Admin();
        $this->updateChecker = new UpdateChecker\UpdateChecker();
        $this->core = new Core\Core();
    }

    public static function get_instance() {

        if (null === self::$instance) {
            self::$instance = new self();
        }
    
        return self::$instance;
    }

    private function init() {
        register_activation_hook(__FILE__, array($this, 'install'));
    }

    public static function install()
    {
        $default = array(
            'tmpl_branding'                     => 'on',
            'tmpl_hide_dashboard_widgets'       => 'on',
            'tmpl_disable_comments'             => 'on'
        );
        update_option('tempel-settings-data', $default);
        
        register_activation_hook(__FILE__, array("Tempel", 'setup'));
    }
}

// add_action('plugins_loaded', function () {
//     new Tempel;
// });

Tempel::get_instance();