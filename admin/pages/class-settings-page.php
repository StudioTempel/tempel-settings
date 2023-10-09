<?php

namespace Tempel\Admin\Pages;


require plugin_dir_path(__FILE__) . '../class-page.php';

use Tempel\Admin\Page;

class SettingsPage extends Page
{
    function render()
    {

        // $this->add_field(
        //     array(
        //         'type'  => 'text',
        //         'id'    => 'tempel_test',
        //         'label' => 'Auto delete messages: ',
        //         'placeholder'  => 'test'
        //     )
        // );
        $this->add_field(
            array(
                'type'  => 'checkbox',
                'id'    => 'hide_dashboard_widgets_bool'
            )
        );

        // var_dump($this->fields);
        $this->print_form();
    }
}
