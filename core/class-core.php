<?php

namespace Tempel\Core;

define('JS_VERSION', null); // Use this for cache busting JS files
define('CSS_VERSION', null); // Use this for cache busting CSS files

class Core
{

    /**
     * Core Constructor
     */
    public function __construct()
    {
        add_action('get_header', array($this, 'tempel_remove_admin_bar_callback_action'));
        add_action('admin_bar_menu', array($this, 'tempel_admin_bar'), 999);
        add_action('admin_bar_menu', array($this, 'tempel_remove_wp_logo'), 999);
        add_action('admin_enqueue_scripts', array($this, 'tempel_styles'));
        add_action('login_form', array($this, 'tempel_login_styles'));
        add_action('admin_init', array($this, 'tempel_disable_commments'));
        add_action('wp_dashboard_setup', array($this, 'tempel_remove_dashboard_widgets'));
    }

    function tempel_remove_wp_logo($wp_admin_bar)
    {
        $wp_admin_bar->remove_node('wp-logo');
    }

    public function tempel_remove_dashboard_widgets()
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

    public function tempel_remove_admin_bar_callback_action()
    {
        remove_action('wp_head', '_admin_bar_bump_cb');
    }

    /**
     * Custom Admin Bar
     */
    public function tempel_admin_bar()
    {
        if (current_user_can('manage_options')) {
            global $wp_admin_bar;
            $wp_admin_bar->add_menu(array(
                'id' => 'studiotempel',
                'title' => '<img src="/wp-content/plugins/tempel-settings/assets/images/admin-logo.svg" width="500" height="600" />',
                'href' => 'studiotempel.nl',
                'meta' => array(
                    'target' => '_blank', // Opens the link with a new tab
                    'title' => __('Studio Tempel'), // Text will be shown on hovering
                ),

            ));
        }
    }

    public function tempel_styles()
    {
        if (!is_admin()) {
            wp_enqueue_style('admin-styles', 'https://studiotempel.nl/branding/admin-bar.css');
        } else {
            wp_enqueue_style('admin-styles', 'https://studiotempel.nl/branding/admin-theme.css');
            wp_enqueue_script('admin', 'https://studiotempel.nl/branding/admin-script.js', array('jquery'), JS_VERSION, true);
            wp_enqueue_style('tempel-settings-styles', '' . plugin_dir_url(__DIR__) . '/dist/css/styles.css');
        }
    }

    public function is_wplogin()
    {
        $ABSPATH_MY = str_replace(array('\\', '/'), DIRECTORY_SEPARATOR, ABSPATH);
        return ((in_array($ABSPATH_MY . 'wp-login.php', get_included_files()) || in_array($ABSPATH_MY . 'wp-register.php', get_included_files())) || (isset($_GLOBALS['pagenow']) && $GLOBALS['pagenow'] === 'wp-login.php') || $_SERVER['PHP_SELF'] == '/wp-login.php');
    }

    public function tempel_login_styles()
    {
        if (is_wplogin()) {
            wp_enqueue_style('login-styles', 'https://studiotempel.nl/branding/login-screen.css');
            wp_enqueue_script('login', 'https://studiotempel.nl/branding/login-script.js', array('jquery'), JS_VERSION, true);
        }
    }

    public function tempel_disable_commments()
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

        // Remove comments page in menu
        add_action('admin_menu', function () {
            remove_menu_page('edit-comments.php');
        });

        add_action('init', function () {
            if (is_admin_bar_showing()) {
                remove_action('admin_bar_menu', 'wp_admin_bar_comments_menu', 60);
            }
        });
    }
}
