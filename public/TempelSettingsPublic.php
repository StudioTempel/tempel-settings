<?php


namespace Tempel\Public;

require_once "includes/SettingBranding.php";
require_once "includes/SettingDisableComments.php";
require_once "includes/SettingDisableDefaultPT.php";
require_once "includes/SettingRemoveDashboardWidgets.php";
require_once "includes/SettingSVGSanitizer.php";
require_once "includes/LoadPluginStyles.php";

use Tempel\Public\SettingBranding;
use Tempel\Public\SettingDisableComments;
use Tempel\Public\SettingDisableDefaultPT;
use Tempel\Public\SettingRemoveDashboardWidgets;
use Tempel\Public\SettingSVGSanitizer;
use Tempel\Public\LoadPluginStyles;

class TempelSettingsPublic
{
    /**
     * Constructor
     */
    public function __construct()
    {
        new LoadPluginStyles();
        
        if ($this->sanitize_checkbox_value($this->return_option('enable_branding'))) {
            new SettingBranding();
        }
        
        if ($this->sanitize_checkbox_value($this->return_option('svg_support'))) {
            new SettingSVGSanitizer();
        }
        
        if ($this->sanitize_checkbox_value($this->return_option('disable_comments'))) {
            new SettingDisableComments();
        }
        
        if ($this->sanitize_checkbox_value($this->return_option('disable_default_pt'))) {
            new SettingDisableDefaultPT();
        }
        
        if ($this->sanitize_checkbox_value($this->return_option('hide_dashboard_widgets'))) {
            new SettingRemoveDashboardWidgets();
        }
        
    }
    
    /**
     * Returns the option value from the database by option name
     *
     * @param string $option_name
     * @since 1.0.0
     */
    function return_option($option_name)
    {
        $option = get_option('tmpl_settings');
        if ($option) {
            return $option[$option_name] ?? false;
        } else {
            return false;
        }
    }
    
    /**
     * Sanitize checkbox value to not return 'on' but true
     *
     * @param string $val
     * @since 1.0.0
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
