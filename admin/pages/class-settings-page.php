<?php

namespace Tempel\Admin\Pages;


require plugin_dir_path(__FILE__) . '../class-page.php';

use Tempel\Admin\Page;

class SettingsPage extends Page
{
    function render()
    {

        // General Section
        $this->add_section(
            array(
                'section_id'        => 'general',
                'title'             => 'General',
            )
        );

        // Branding Category
        $this->add_category(
            array(
                'category_id'       => 'tmpl_branding',
                'title'             => 'Branding',
                'subtitle'      => 'Branding subtitle',
                'section_id'        => 'general',
            )
        );

        // Branding Field
        $this->add_field(
            array(
                'type'              => 'checkbox',
                'field_id'          => 'tmpl_branding',
                'label'             => 'Enable branding',
                'category_id'       => 'tmpl_branding',
            )
        );

        // Comments Category
        $this->add_category(
            array(
                'category_id'       => 'tmpl_comments',
                'title'             => 'Comments',
                'subtitle'      => 'Comments subtitle',
                'section_id'        => 'general',
            )
        );

        // Comments Field
        $this->add_field(
            array(
                'type'              => 'checkbox',
                'field_id'          => 'tmpl_disable_comments',
                'label'             => 'Disable built in comments and post type',
                'category_id'       => 'tmpl_comments',
            )
        );

        // Dashboard Category
        $this->add_category(
            array(
                'category_id'       => 'tmpl_dashboard',
                'title'             => 'Dashboard',
                'subtitle'      => 'Dashboard subtitle',
                'section_id'        => 'general',
            )
        );

        // Dashboard Field
        $this->add_field(
            array(
                'type'              => 'checkbox',
                'field_id'          => 'tmpl_hide_dashboard_widgets',
                'label'             => 'Hide dashboard widgets',
                'category_id'       => 'tmpl_dashboard',
            )
        );

        // General Section
        $this->add_section(
            array(
                'section_id'        => 'widget',
                'title'             => 'Widget',
            )
        );

        $this->print_form();
    }
}
