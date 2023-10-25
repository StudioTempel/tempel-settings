<?php

namespace Tempel\Admin;

abstract class Page
{

    protected $slug;

    protected $menu_title;

    protected $capability;

    protected $icon_url;

    protected $sections = array();

    function __construct($slug, $page_title, $menu_title, $icon_url, $position, $render = true, $capability = "manage_options")
    {

        $this->slug = $slug;

        add_menu_page(
            $page_title,                                // Page title
            $menu_title,                                // Menu title
            $capability,                                // Capability
            $slug,                                      // Menu slug
            $render ? array($this, 'render') : null,    // Callback
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

                    if ($this->sections) {
                        $this->print_sections();
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
    ?>
        <div class="tmpl-header">
            <h1><?= esc_html(get_admin_page_title()); ?></h1>
        </div>
    <?php
    }

    public function print_footer()
    {
        echo '';
    }

    public function print_sections()
    {
        foreach ($this->sections as $section) {
            $this->add_section_callback($section);
        }
    }

    public function add_section_callback($section)
    {
    ?>
        <div class="tmpl_section">
            <div class="section">
                <h2><?= $section['title']; ?></h2>
            </div>
            <div class="categories">
                <?php
                foreach ($section['categories'] as $category) {
                    $this->add_category_callback($category);
                }
                ?>
            </div>
        </div>
    <?php
    }

    public function add_category_callback($category)
    {
    ?>
        <div class="tmpl_category">
            <div class="category">
                <h3><?= $category['title']; ?></h3>
                <p><?= $category['subtitle']; ?></p>
            </div>
            <div class="fields">
                <?php
                foreach ($category['fields'] as $field) {
                    $this->add_field_callback($field);
                }
                ?>
            </div>
        </div>
<?php
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

    public function add_text_field($args)
    {
        echo "
        <div class='form-group'>
            <input type='text' name='tempel-settings-data[" . $args['field_id'] . "]' placeholder='" . $args['placeholder'] . "' />
        </div>        
        ";
    }



    public function add_checkbox_field($args)
    {
        echo "
        <div class='field-wrap'>
            <div class='form-group'>
                <input type='checkbox' id='" . $args['field_id'] . "' name='tempel-settings-data[" . $args['field_id'] . "]' " . $this->is_checked($args['field_id']) . "/>
                <label for='" . $args['field_id'] . "'></label>
            </div>
            <div class='field-label'>
                <p>" . $args['label'] . "</p>
            </div>

        </div>
        ";
    }

    public function is_checked($args)
    {
        $option = get_option('tempel-settings-data');
        if ($option) {
            $checkbox_value = $option[$args] ?? false;
        } else {
            $checkbox_value = false;
        }

        return checked("on", $checkbox_value, false);
    }

    public function add_section($args)
    {
        $default_args = array(
            'section_id'                => '',
            'title'             => '',
            'categories'        => array(),
        );

        $data = array_merge($default_args, $args);
        array_push($this->sections, $data);
    }

    public function add_category($args)
    {
        $default_args = array(
            'category_id'                => '',
            'title'             => '',
            'subtitle'          => '',
            'section_id'        => '',
            'fields'            => array(),
        );

        $data = array_merge($default_args, $args);


        foreach ($this->sections as &$section) {
                
            if ($section['section_id'] == $data['section_id']) {
                array_push($section['categories'], $data);
            }
        }

        // echo '<br>';
        // print_r($this->sections);
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
            'placeholder'    => '',
            'category_id'       => '',
        );
        $data         = array_merge($default_args, $args);

        foreach ($this->sections as &$section) {
            foreach ($section['categories'] as &$category) {
                if ($category['category_id'] == $data['category_id']) {
                    array_push($category['fields'], $data);
                }
            }
        }
    }
}
