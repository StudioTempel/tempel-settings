<?php

namespace Tempel\Core;

define('JS_VERSION', null); // Use this for cache busting JS files
define('CSS_VERSION', null); // Use this for cache busting CSS files

require_once "includes/class-svg-sanitizer.php";
require_once "includes/styling.php";

class Core
{

    /**
     * Core Constructor
     */
    public function __construct()
    {
        add_action('admin_enqueue_scripts', array($this, 'plugin_styles'));

        // Loads
        if ($this->sanitize_checkbox_value($this->return_option('tmpl_branding'))) {
            new Styling();
        }

        new TMPL_SVGSanitizer();

        if ($this->sanitize_checkbox_value($this->return_option('tmpl_disable_comments'))) {
            add_action('admin_init', array($this, 'tmpl_disable_commments'));

            add_action('admin_menu', array($this, 'tmpl_remove_default_post_type'));
            add_action('admin_bar_menu', array($this, 'tmpl_remove_default_post_type_menu_bar'), 999);
            add_action('wp_dashboard_setup', array($this, 'tmpl_remove_draft_widget'), 999);
            add_action('admin_footer', array($this, 'tmpl_remove_add_new_post_href_in_admin_bar'));
            // add_action('wp_footer', 'tmpl_remove_add_new_post_href_in_admin_bar');
            add_action('init', array($this, 'tmpl_change_wp_object'));
        }

        // Hide dashboard widgets
        if ($this->sanitize_checkbox_value($this->return_option('tmpl_hide_dashboard_widgets'))) {
            add_action('wp_dashboard_setup', array($this, 'tempel_remove_dashboard_widgets'));
        }
    }

    /**
     * return option by name
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

    public function plugin_styles()
    {
        if (is_admin()) {
            wp_enqueue_style('tempel-settings-styles', plugin_dir_url(__DIR__) . '/dist/css/styles.css');
        }
    }

    /**
     * Sanitize checkbox value to not return 'on' but true
     */
    function sanitize_checkbox_value($val)
    {
        if ($val == 'on') {
            return true;
        } else {
            return false;
        }
    }





    function tempel_remove_dashboard_widgets()
    {
        global $wp_meta_boxes;
        unset($wp_meta_boxes['dashboard']['normal']['core']['dashboard_right_now']); // Right Now
        unset($wp_meta_boxes['dashboard']['normal']['core']['dashboard_activity']); // Activity
        unset($wp_meta_boxes['dashboard']['normal']['core']['dashboard_recent_comments']); // Recent Comments
        unset($wp_meta_boxes['dashboard']['normal']['core']['dashboard_incoming_links']); // Incoming Links
        unset($wp_meta_boxes['dashboard']['normal']['core']['dashboard_plugins']); // Plugins
        unset($wp_meta_boxes['dashboard']['side']['core']['dashboard_quick_press']); // Quick Press
        unset($wp_meta_boxes['dashboard']['side']['core']['dashboard_recent_drafts']); // Recent Drafts
        unset($wp_meta_boxes['dashboard']['side']['core']['dashboard_primary']); // WordPress blog
        unset($wp_meta_boxes['dashboard']['side']['core']['dashboard_secondary']); // Other WordPress News
    }



    /**
     * 
     *  START DISABLE COMMENTS
     * 
     */

    public function tmpl_disable_commments()
    {
        // Redirect any user trying to access comments page
        global $pagenow;

        if ($pagenow === 'edit-comments.php') {
            wp_redirect(admin_url());
            exit;
        }

        // Remove comments metabox from dashboard
        remove_meta_box('dashboard_recent_comments', 'dashboard', 'normal');

        // Disable support for comments and trackbacks in post types
        foreach (get_post_types() as $post_type) {
            if (post_type_supports($post_type, 'comments')) {
                remove_post_type_support($post_type, 'comments');
                remove_post_type_support($post_type, 'trackbacks');
            }
        }

        // Close comments on the front-end
        add_filter('comments_open', '__return_false', 20, 2);
        add_filter('pings_open', '__return_false', 20, 2);

        // Hide existing comments
        add_filter('comments_array', '__return_empty_array', 10, 2);

        remove_menu_page('edit-comments.php');

        if (is_admin_bar_showing()) {
            remove_action('admin_bar_menu', 'wp_admin_bar_comments_menu', 60);
        }
    }

    function tmpl_remove_default_post_type()
    {
        remove_menu_page('edit.php');
    }

    function tmpl_remove_default_post_type_menu_bar($wp_admin_bar)
    {
        $wp_admin_bar->remove_node('new-post');
    }

    // function tmpl_remove_frontend_post_href()
    // {
    //     if (is_user_logged_in()) {
    //         add_action('wp_footer', 'tmpl_remove_add_new_post_href_in_admin_bar');
    //     }
    // }

    function tmpl_remove_add_new_post_href_in_admin_bar()
    {
?>
        <script type="text/javascript">
            function tmpl_remove_add_new_post_href_in_admin_bar() {
                var add_new = document.getElementById('wp-admin-bar-new-content');
                if (!add_new) return;
                var add_new_a = add_new.getElementsByTagName('a')[0];
                if (add_new_a) add_new_a.setAttribute('href', '#!');
            }
            tmpl_remove_add_new_post_href_in_admin_bar();
        </script>
<?php
    }

    function tmpl_remove_draft_widget()
    {
        remove_meta_box('dashboard_quick_press', 'dashboard', 'side');
    }

    function tmpl_change_wp_object()
    {
        $object = get_post_type_object('post');
        $object->public = false;
        $object->show_in_nav_menus = false;
    }

    /**
     * 
     *  END DISABLE COMMENTS
     * 
     */
}
