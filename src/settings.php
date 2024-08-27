<?php

namespace Tempel;

require_once 'includes/settings/branding.php';
require_once 'includes/settings/disable-comments.php';
require_once 'includes/settings/disable-post.php';
require_once 'includes/settings/remove-dashboard-widgets.php';
require_once 'includes/settings/svg-support.php';

use Tempel\Settings\Branding;
use Tempel\Settings\Disable_Comments;
use Tempel\Settings\Disable_Post;
use Tempel\Settings\Remove_Dashboard_Widgets;
use Tempel\Settings\SVG_Support;

require_once 'includes/helper.php';

if(!class_exists('Settings')) {
    class Settings {
        public static function load_settings() : void
        {
            if(Helper::sanitize_checkbox_value(Helper::return_option('enable_branding'))) {
                new Branding();
            }
            
            if(Helper::sanitize_checkbox_value(Helper::return_option('disable_comments'))) {
                new Disable_Comments();
            }
            
            if(Helper::sanitize_checkbox_value(Helper::return_option('disable_default_pt'))) {
                new Disable_Post();
            }
            
            if(Helper::sanitize_checkbox_value(Helper::return_option('hide_dashboard_widgets'))) {
                new Remove_Dashboard_Widgets();
            }
            
            if(Helper::sanitize_checkbox_value(Helper::return_option('svg_support'))) {
                new SVG_Support();
            }
        }
    }
}