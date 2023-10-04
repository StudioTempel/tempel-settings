<?php

namespace Tempel\Admin\Pages;


require plugin_dir_path(__FILE__) . '../class-page.php';

use Tempel\Admin\Page;

class SettingsPage extends Page
{
    function render()
    {
?>
        <div id="tempel-settings-wrap" class="tmpl-wrap">
            <div class="tmpl-header">

            </div>
            <div class="tmpl-content">
                <form action="options.php" method="post">
                    <?php
                    settings_fields('tempel-settings');
                    $this->tempel_settings_init();

                    do_settings_sections('tempel-settings');

                    submit_button("Save");
                    ?>
                </form>
            </div>
            <div class="tmpl-footer">

            </div>


        </div>
<?php
    }

    public function tempel_settings_init()
    {
        add_settings_section(
            'tempel-settings-section',                                              // ID
            '',                                                   // Title
            array($this, 'tempel_section_callback'),                                // Callback
            'tempel-settings'                                                       // Page
        );
        add_settings_field(
            'tempel-settings-field',                                                // ID
            'Verberg Dashboard Widgets',                                            // Title
            array($this, 'tempel_settings_callback'),                               // Callback
            'tempel-settings',                                                      // Page
            'tempel-settings-section'                                               // Section
        );
    }

    function tempel_section_callback()
    {
        echo '<h1>' . esc_html(get_admin_page_title()) . '</h1>';
    }

    function tempel_settings_callback()
    {
        $option = get_option('tempel-settings-data');
        if ($option) {
            $hide_dashboard_widgets_bool = $option['hide_dashboard_widgets_bool'];
        } else {
            $hide_dashboard_widgets_bool = false;
        }

        $html = "
        
        <div class='form-group'>
            <input type='checkbox' id='hide_dashboard_widgets' name='tempel-settings-data[hide_dashboard_widgets_bool]' " . checked("on", $hide_dashboard_widgets_bool, false) . "/>
            <label for='hide_dashboard_widgets'></label>
        </div>
        ";
        echo $html;
    }
}
