<?php

namespace Tempel\Admin\Pages;


require plugin_dir_path(__FILE__) . '../class-page.php';

use Tempel\Admin\Page;

class SettingsPage extends Page
{
    function render()
    {

        /**
         * Sections
         * 
         * !REQUIRED: ID, Title
         */

        /**
         * Categories
         * 
         * !REQUIRED: ID, Title, Section ID
         */

        /**
         * Fields
         * 
         * !REQUIRED: Type, Field ID, Label, Category ID
         */

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
                'subtitle'          => 'Activeer Studio Tempel branding',
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
                'subtitle'          => 'Activeer deze instelling om de commments en standaard berichten post type uit te schakelen',
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
                'subtitle'          => 'Verberg alle ongebruikte dashboard widgets',
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
                'section_id'        => 'extra',
                'title'             => 'Extra',
            )
        );

        $this->add_category(
            array(
                'category_id'       => 'tmpl_widget',
                'title'             => 'Widget',
                'subtitle'          => 'Voeg een support widget toe aan het dashboard',
                'section_id'        => 'extra',
            )
        );

        $this->add_field(
            array(
                'field_id'          => 'tmpl_enable_widget',
                'category_id'       => 'tmpl_widget',
                'type'              => 'checkbox',
                'label'             => 'Enable widget',
            )
        );

        $this->add_category(
            array(
                'category_id'       => 'tmpl_svg_support',
                'title'             => 'SVG Support',
                'subtitle'          => 'Voeg svg support toe aan de media uploader',
                'section_id'        => 'extra',
            )
        );

        $this->add_field(
            array(
                'field_id'          => 'tmpl_enable_svg',
                'category_id'       => 'tmpl_svg_support',
                'type'              => 'checkbox',
                'label'             => 'Enable SVG Support',
            )
        );

        $this->print_form();
    }
}
