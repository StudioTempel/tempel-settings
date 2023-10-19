<?php

namespace Tempel\Admin\Pages;


require plugin_dir_path(__FILE__) . '../class-page.php';

use Tempel\Admin\Page;

class SettingsPage extends Page
{
    function render()
    {
        $this->add_field(
            array(
                'type' => 'checkbox',
                'id' => 'tmpl_disable_comments',
                'label' => 'Disable comments: ',
            )
        );
        $this->add_field(
            array(
                'type'  => 'checkbox',
                'id'    => 'tmpl_hide_dashboard_widgets',
                'label' => 'Hide dashboard widgets: ',
            )
        );

        $this->print_form();
    }
}
