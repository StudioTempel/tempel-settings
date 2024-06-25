<?php

namespace Tempel\Core;

require_once "settings/branding.php";
require_once "settings/disable_comments.php";
require_once "settings/disable_default_pt.php";
require_once "settings/remove_dashboard_widgets.php";
require_once "settings/svg_sanitizer.php";
require_once "includes/load_styles.php";

// widgets
require_once "widgets/status_widget.php";
require_once "widgets/conversion_widget.php";
require_once "widgets/support_widget.php";

class Core
{
    /**
     * Constructor
     */
    public function __construct()
    {
        new LoadPluginStyles();

        if ($this->sanitize_checkbox_value($this->return_option('tmpl_branding'))) {
            new Branding();
        }

        if ($this->sanitize_checkbox_value($this->return_option('tmpl_enable_svg'))) {
            new SVGSanitizer();
        }

        if ($this->sanitize_checkbox_value($this->return_option('tmpl_disable_comments'))) {
            new DisableComments();
        }

        if ($this->sanitize_checkbox_value($this->return_option('tmpl_disable_default_posts'))) {
            new DisableDefaultPT();
        }

        if ($this->sanitize_checkbox_value($this->return_option('tmpl_hide_dashboard_widgets'))) {
            new RemoveDashboardWidgets();
        }

        if ($this->sanitize_checkbox_value($this->return_option('tmpl_enable_widget'))) {
        }

        new Widgets\StatusWidget();

        new Widgets\SupportWidget();
        
        if (class_exists('GFForms')) {
            new Widgets\ConversionWidget();
        }
    }

    /**
     * Returns the option value from the database by option name
     * 
     * @since 1.0.0
     * @param string $option_name
     */
    function return_option($option_name)
    {
        $option = get_option('tempel-settings-data');
        if ($option) {
            return $option[$option_name] ?? false;
        } else {
            return false;
        }
    }
    /**
     * Sanitize checkbox value to not return 'on' but true
     * 
     * @since 1.0.0
     * @param string $val
     */
    function sanitize_checkbox_value($val)
    {
        if ($val == 'on') {
            return true;
        } else {
            return false;
        }
    }
}
