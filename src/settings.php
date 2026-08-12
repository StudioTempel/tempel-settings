<?php

namespace Tempel;

require_once TEMPEL_SETTINGS_DIR . 'src/includes/settings/branding.php';
require_once TEMPEL_SETTINGS_DIR . 'src/includes/settings/disable-comments.php';
require_once TEMPEL_SETTINGS_DIR . 'src/includes/settings/disable-post.php';
require_once TEMPEL_SETTINGS_DIR . 'src/includes/settings/remove-dashboard-widgets.php';
require_once TEMPEL_SETTINGS_DIR . 'src/includes/settings/skip-bundled-themes.php';
require_once TEMPEL_SETTINGS_DIR . 'src/includes/settings/svg-support.php';
require_once TEMPEL_SETTINGS_DIR . 'src/includes/settings/gf-bag-address.php';
require_once TEMPEL_SETTINGS_DIR . 'src/includes/settings/performance.php';
require_once TEMPEL_SETTINGS_DIR . 'src/includes/settings/taxonomy-order.php';
require_once TEMPEL_SETTINGS_DIR . 'src/includes/settings/duplicate-content.php';

require_once TEMPEL_SETTINGS_DIR . 'src/includes/helper-functions.php';

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

            if(sanitize_checkbox_value(return_option('tmpl_settings', 'skip_bundled_themes'))) {
                new Skip_Bundled_Themes();
            }
            
            if(sanitize_checkbox_value(return_option('tmpl_settings', 'svg_support'))) {
                new SVG_Support();
            }

            if(sanitize_checkbox_value(return_option('tmpl_settings', 'taxonomy_order'))) {
                new Taxonomy_Order();
            }

            if(sanitize_checkbox_value(return_option('tmpl_settings', 'duplicate_content'))) {
                new Duplicate_Content();
            }

            if(sanitize_checkbox_value(return_option('tmpl_settings', 'performance_enabled'))) {
                new Performance();
            }

            if (
                class_exists('GFForms') &&
                sanitize_checkbox_value(return_option('tmpl_settings', 'gf_bag_address_enabled'))
            ) {
                new GF_BAG_Address();
            }
        }
    }
}
