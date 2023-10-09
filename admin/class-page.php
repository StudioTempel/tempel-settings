<?php

namespace Tempel\Admin;

abstract class Page
{

    protected $slug;

    protected $menu_title;

    protected $capability;

    protected $icon_url;

    protected $fields;

    protected $sections = array();

    protected $section_id;

    function __construct($slug, $page_title, $menu_title, $icon_url, $position, $render = true, $capability = "manage_options")
    {

        $this->slug = $slug;
        $this->fields = array();
        $this->sections = array();


        add_menu_page(
            $page_title,                                // Page title
            $menu_title,                                // Menu title
            $capability,                                // Capability
            $slug,                                      // Menu slug
            $render ? array($this, 'render') : null,                     // Callback
            $icon_url,                                  // Icon URL
            $position                                   // Position
        );
    }
    public function print_form()
    {
?>
        <div id="tempel-settings-wrap" class="tmpl-wrap">
            <?php $this->print_header(); ?>
            <div class="tmpl-content">
                <form class="tmpl-form" action="options.php" method="post">
                    <?php
                    settings_fields('tempel-settings');

                    if ($this->fields) {
                        $this->print_fields();
                    }

                    do_settings_sections('tempel-settings');

                    submit_button("Save")
                    ?>
                </form>
            </div>
            <div class="tmpl-footer">
                <?php $this->print_footer(); ?>
            </div>
        </div>
<?php
    }

    public function print_header()
    {
        echo '
        <div class="tmpl-header">
        <h1>' . esc_html(get_admin_page_title()) . '</h1>        
        </div>'
        ;
    }

    public function print_footer()
    {
        echo '';
    }

    public function print_fields()
    {
        foreach ($this->fields as $field) {
            $this->add_field_callback($field);
        }
    }

    public function add_field_callback($args)
    {
        switch ($args['type']) {
            case 'text':
                $this->add_text_field($args);
                break;

            case 'checkbox':
                $this->add_checkbox_field($args);
                break;
        }
    }

    public function add_field($args)
    {
        $default_args = array(
            'type'           => '',
            'id'             => '',
            'label'          => '',
            'tip'            => '',
            'min'            => '',
            'max'            => '',
            'input_class'    => '',
            'class'          => '',
            'options'        => array('Select a value' => ''),
            'default_option' => '',
            'autocomplete'   => 'on',
            'placeholder'    => ''
        );
        $data         = array_merge($default_args, $args);
        $data['section_id'] = $this->section_id;
        array_push($this->fields, $data);
    }

    public function add_section($id, $title = '')
    {
        array_push($this->sections, array($id, $title));
        $this->section_id = $id;
    }

    public function add_text_field($args)
    {
        echo "
        <div class='form-group'>
            <input type='text' name='tempel-settings-data[" . $args['id'] . "]' placeholder='" . $args['placeholder'] . "' />
        </div>        
        ";
    }

    public function add_checkbox_field($args)
    {
        $option = get_option('tempel-settings-data');
        if ($option) {
            $checkbox_value = $option[$args['id']];
        } else {
            $checkbox_value = false;
        }

        echo "
        <div class='form-group'>
            <input type='checkbox' id='hide_dashboard_widgets' name='tempel-settings-data[" . $args['id'] . "]' " . checked("on", $checkbox_value, false) . "/>
            <label for='hide_dashboard_widgets'></label>
        </div>
        ";
        echo get_option("tempel-settings-data[" . $args['id'] . "]");
    }
}
