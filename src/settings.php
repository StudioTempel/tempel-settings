<?php

namespace Tempel;

require_once 'includes/settings/branding.php';
require_once 'includes/settings/disable-comments.php';
require_once 'includes/settings/disable-post.php';
require_once 'includes/settings/remove-dashboard-widgets.php';
require_once 'includes/settings/svg-support.php';

require_once 'includes/helper-functions.php';

if(!class_exists('Settings')) {
    class Settings {
        public static function load_settings()
        {
            if(sanitize_checkbox_value(return_option('tmpl_settings', 'enable_branding'))) {
                new Branding();
            }
            
            if(sanitize_checkbox_value(return_option('tmpl_settings', 'disable_comments'))) {
                new Disable_Comments();
            }
            
            if(sanitize_checkbox_value(return_option('tmpl_settings', 'disable_default_pt'))) {
                new Disable_Post();
            }
            
            if(sanitize_checkbox_value(return_option('tmpl_settings', 'hide_dashboard_widgets'))) {
                new Remove_Dashboard_Widgets();
            }
            
            if(sanitize_checkbox_value(return_option('tmpl_settings', 'svg_support'))) {
                new SVG_Support();
            }
        }
    }
}