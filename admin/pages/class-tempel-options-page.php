<?php

namespace Tempel_Plugin;

class TempelSettingsPage
{

    function __construct()
    {
        // add_action('admin_menu', array($this, 'tempelOptionsPage'));
        add_action('admin_init', array($this, 'tempel_settings_init'));
    }

    // function tempelOptionsPage()
    // {
    //     add_menu_page(
    //         __('Tempel settings', 'tempel'),                                        // Page title
    //         __('Tempel settings', 'tempel'),                                        // Menu title
    //         'manage_options',                                                       // Capability
    //         'tempel-settings',                                                      // Menu slug                
    //         array($this, 'tempelSettingsPage'),                                     // Callback function
    //         plugin_dir_url(__FILE__) . '../assets/images/admin-logo.svg',           // Icon URL
    //         99                                                                      // Position
    //     );
    // }

    function tempel_settings_init()
    {
        register_setting(
            'tempel-settings',                                                      // Option group
            'tempel-settings-data',                                                 // Option name
        );

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

    function render()
    {
        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have sufficient permissions to access this page.', 'tempel'));
        }
?>
        <div id="tempel-settings-wrap" class="tmpl-wrap">
            <div class="tmpl-header">

            </div>
            <div class="tmpl-content">
                <form action="options.php" method="post">
                    <?php
                    settings_fields('tempel-settings');
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
}

new TempelSettingsPage;
