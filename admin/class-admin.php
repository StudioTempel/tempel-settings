<?php

namespace Tempel\Admin;

require 'pages/class-settings-page.php';

use Tempel\Admin\Pages;

class Admin
{

    public $pages = array();


    public function __construct()
    {
        add_action('admin_menu', array($this, 'add_menu_pages'));
        add_action('admin_init', array($this, 'tempel_register_settings'));
    }

    public function add_menu_pages()
    {
        $this->pages['tempel-options'] = new Pages\SettingsPage('tempel-settings', 'Tempel Settings', 'Tempel Settings', $this->get_menu_icon(), 99);
    }
    public function tempel_register_settings()
    {
        register_setting(
            'tempel-settings',
            'tempel-settings-data'
        );
    }

    public function get_menu_icon()
    {
        return plugins_url('assets/images/admin-logo.svg', dirname(__FILE__));
    }
}
