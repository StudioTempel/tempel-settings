<?php

namespace Tempel\Admin;

require 'pages/class-settings-page.php';

use Tempel\Admin\Pages;

class Admin
{

    public static $pages = array(
        'settings_page'
    );
    

    public function __construct()
    {
        add_action( 'admin_menu', array( $this, 'add_menu_pages'));
    }

    public function add_menu_pages() {
        $this->pages['tempel-settings'] = new Pages\SettingsPage( 'tempel-settings', 'Tempel settings', 'Tempel settings', 'manage_options', null, 99 );
    }
}
