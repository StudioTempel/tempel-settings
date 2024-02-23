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
 * Description:       Plugin that compliments custom built themes produced by Studio Tempel
 * Version:           1.8
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
}

// add_action('plugins_loaded', function () {
//     new Tempel;
// });

Tempel::get_instance();
