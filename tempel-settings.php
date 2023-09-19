<?php
/**
 * StudioTempel settings
 *
 * @package           tempel-settings
 * @author            Studio Tempel
 * @copyright         2023 Studio Tempel
 * @license           GPL-2.0-or-later
 *
 * @wordpress-plugin
 * Plugin Name:       Tempel settings
 * Plugin URI:        https://studiotempel.nl/tempel-settings
 * Description:       Simple plugin with settings for login branding, admin branding, disabling commens and more.
 * Version:           1.4
 *
 * Requires at least: 6
 * Requires PHP:      8
 * Author:            Studio Tempel
 * Author URI:        https://studiotempel.nl
 * Text Domain:       tempel-settings
 * License:           GPL v2 or later
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Update URI:        https://studiotempel.nl/tempel-settings
 */

define('JS_VERSION', null); // Use this for cache busting JS files
define('CSS_VERSION', null); // Use this for cache busting CSS files

$dir = plugin_dir_path(__FILE__);
$logo = $dir . '/admin-logo.svg';


/*Remove WordPress menu from admin bar*/
add_action('admin_bar_menu', 'remove_wp_logo', 999);
function remove_wp_logo($wp_admin_bar)
{
    $wp_admin_bar->remove_node('wp-logo');
}


if (function_exists('acf_add_local_field_group')):

    acf_add_local_field_group(array(
        'key' => 'group_63f355fa5efa7',
        'title' => 'Tempel settings',
        'fields' => array(
            array(
                'key' => 'field_63f355fab0511',
                'label' => 'Verberg dashboard widgets',
                'name' => 'hide_dashboard_widgets',
                'aria-label' => '',
                'type' => 'true_false',
                'instructions' => '',
                'required' => 0,
                'conditional_logic' => 0,
                'wrapper' => array(
                    'width' => '',
                    'class' => '',
                    'id' => '',
                ),
                'message' => '',
                'default_value' => 0,
                'ui_on_text' => '',
                'ui_off_text' => '',
                'ui' => 1,
            ),
        ),
        'location' => array(
            array(
                array(
                    'param' => 'options_page',
                    'operator' => '==',
                    'value' => 'tempel-settings',
                ),
            ),
        ),
        'menu_order' => 0,
        'position' => 'normal',
        'style' => 'seamless',
        'label_placement' => 'top',
        'instruction_placement' => 'label',
        'hide_on_screen' => '',
        'active' => true,
        'description' => '',
        'show_in_rest' => 0,
    ));

endif;

$dashboard_widgets = get_field('hide_dashboard_widgets', 'option');
if ($dashboard_widgets) {
    function remove_dashboard_widgets()
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

    add_action('wp_dashboard_setup', 'remove_dashboard_widgets');
}


add_action('get_header', 'my_filter_head');

function my_filter_head()
{
    remove_action('wp_head', '_admin_bar_bump_cb');
}




function myplugin_admin_bar_menu()
{
    if (current_user_can('manage_options')) {
        global $wp_admin_bar;
        $wp_admin_bar->add_menu(array(
            'id' => 'studiotempel',
            'title' => '<img src="/wp-content/plugins/tempel-settings/admin-logo.svg" width="500" height="600" />',
            'href' => 'studiotempel.nl',
            'meta' => array(
                'target' => '_blank', // Opens the link with a new tab
                'title' => __('Studio Tempel'), // Text will be shown on hovering
            ),

        ));
    }
}


add_action('admin_bar_menu', 'myplugin_admin_bar_menu');


if (!is_admin()) {
    wp_enqueue_style('admin-styles', 'https://studiotempel.nl/branding/admin-bar.css');
} else {
    wp_enqueue_style('admin-styles', 'https://studiotempel.nl/branding/admin-theme.css');
    wp_enqueue_script('admin', 'https://studiotempel.nl/branding/admin-script.js', array('jquery'), JS_VERSION, true);
}

function is_wplogin(){
    $ABSPATH_MY = str_replace(array('\\','/'), DIRECTORY_SEPARATOR, ABSPATH);
    return ((in_array($ABSPATH_MY.'wp-login.php', get_included_files()) || in_array($ABSPATH_MY.'wp-register.php', get_included_files()) ) || (isset($_GLOBALS['pagenow']) && $GLOBALS['pagenow'] === 'wp-login.php') || $_SERVER['PHP_SELF']== '/wp-login.php');
}

if( is_wplogin() ){
    wp_enqueue_style('login-styles', 'https://studiotempel.nl/branding/login-screen.css');
    wp_enqueue_script('login', 'https://studiotempel.nl/branding/login-script.js', array('jquery'), JS_VERSION, true);
}

//if ($GLOBALS['pagenow'] === 'wp-login.php') {
//
//}


function my_acf_op_init($icon)
{

// Check function exists.
    if (function_exists('acf_add_options_page')) {

// Register options page.
        $option_page = acf_add_options_page(array(
            'page_title' => 'Tempel settings',
            'menu_title' => 'Tempel settings',
            'menu_slug' => 'tempel-settings',
            'capability' => 'manage_options',
            'position' => 1000.1,
            'redirect' => true,
            'icon_url' => '/wp-content/plugins/tempel-settings/admin-logo.svg',
            'update_button' => 'Bijwerken',
            'updated_message' => 'Tempel settings bijgewerkt',
        ));
    }
}

add_action('acf/init', 'my_acf_op_init', $logo);

//Remove all comments on the website

add_action('admin_init', function () {
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
});

// Close comments on the front-end
add_filter('comments_open', '__return_false', 20, 2);
add_filter('pings_open', '__return_false', 20, 2);

// Hide existing comments
add_filter('comments_array', '__return_empty_array', 10, 2);

// Remove comments page in menu
add_action('admin_menu', function () {
    remove_menu_page('edit-comments.php');
});

// Remove comments links from admin bar
add_action('init', function () {
    if (is_admin_bar_showing()) {
        remove_action('admin_bar_menu', 'wp_admin_bar_comments_menu', 60);
    }
});

function remove_comments()
{
    global $wp_admin_bar;
    $wp_admin_bar->remove_menu('comments');
}

add_action('wp_before_admin_bar_render', 'remove_comments');

defined('ABSPATH') || exit;

//include 'updatechecker.php';


if (!class_exists('tempelUpdateChecker')) {

    class tempelUpdateChecker
    {

        public $plugin_slug;
        public $version;
        public $cache_key;
        public $cache_allowed;

        public function __construct()
        {

            $this->plugin_slug = plugin_basename(__DIR__);
            $this->version = '1.0';
            $this->cache_key = 'tempel_custom_upd';
            $this->cache_allowed = false;

            add_filter('plugins_api', array($this, 'info'), 20, 3);
            add_filter('site_transient_update_plugins', array($this, 'update'));
            add_action('upgrader_process_complete', array($this, 'purge'), 10, 2);

        }

        public function request()
        {

            $remote = get_transient($this->cache_key);

            if (false === $remote || !$this->cache_allowed) {

                $remote = wp_remote_get(
                    'https://studiotempel.nl/tempel-settings/info.json',
                    array(
                        'timeout' => 10,
                        'headers' => array(
                            'Accept' => 'application/json'
                        )
                    )
                );

                if (
                    is_wp_error($remote)
                    || 200 !== wp_remote_retrieve_response_code($remote)
                    || empty(wp_remote_retrieve_body($remote))
                ) {
                    return false;
                }

                set_transient($this->cache_key, $remote, DAY_IN_SECONDS);

            }

            $remote = json_decode(wp_remote_retrieve_body($remote));

            return $remote;

        }


        function info($res, $action, $args)
        {

// print_r( $action );
// print_r( $args );

// do nothing if you're not getting plugin information right now
            if ('plugin_information' !== $action) {
                return $res;
            }

// do nothing if it is not our plugin
            if ($this->plugin_slug !== $args->slug) {
                return $res;
            }

// get updates
            $remote = $this->request();

            if (!$remote) {
                return $res;
            }

            $res = new stdClass();

            $res->name = $remote->name;
            $res->slug = $remote->slug;
            $res->version = $remote->version;
            $res->tested = $remote->tested;
            $res->requires = $remote->requires;
            $res->author = $remote->author;
            $res->author_profile = $remote->author_profile;
            $res->download_link = $remote->download_url;
            $res->trunk = $remote->download_url;
            $res->requires_php = $remote->requires_php;
            $res->last_updated = $remote->last_updated;

            $res->sections = array(
                'description' => $remote->sections->description,
                'installation' => $remote->sections->installation,
                'changelog' => $remote->sections->changelog
            );

            if (!empty($remote->banners)) {
                $res->banners = array(
                    'low' => $remote->banners->low,
                    'high' => $remote->banners->high
                );
            }

            return $res;

        }

        public function update($transient)
        {

            if (empty($transient->checked)) {
                return $transient;
            }

            $remote = $this->request();

            if (
                $remote
                && version_compare($this->version, $remote->version, '<')
                && version_compare($remote->requires, get_bloginfo('version'), '<=')
                && version_compare($remote->requires_php, PHP_VERSION, '<')
            ) {
                $res = new stdClass();
                $res->slug = $this->plugin_slug;
                $res->plugin = plugin_basename(__FILE__); // tempel-update-plugin/tempel-update-plugin.php
                $res->new_version = $remote->version;
                $res->tested = $remote->tested;
                $res->package = $remote->download_url;

                $transient->response[$res->plugin] = $res;

            }

            return $transient;

        }

        public function purge($upgrader, $options)
        {

            if (
                $this->cache_allowed
                && 'update' === $options['action']
                && 'plugin' === $options['type']
            ) {
// just clean the cache when new plugin version is installed
                delete_transient($this->cache_key);
            }

        }


    }

    new tempelUpdateChecker();

}
