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
 * Version:           1.5
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

require 'admin/class-admin.php';
require 'core/class-core.php';
require 'core/class-update-checker.php';

use Tempel\Admin;
use Tempel\Core;
use Tempel\UpdateChecker;

class Tempel {

    public $admin;

    public $core;

    public $updateChecker;

    /**
    * Tempel Constructor
    */
    public function __construct() {
        $this->init();
    }

    /**
    * Tempel Init
    */
    private function init() {
        if(is_admin()) {
            $this->admin = new Admin\Admin();
            $this->core = new Core\Core();
            $this->updateChecker = new UpdateChecker\UpdateChecker();
        }
    }
}

add_action('plugins_loaded', function() {
    new Tempel;
});