<?php

namespace Tempel\Core;
class Styling
{
    public function __construct()
    {
        add_action('get_header', array($this, 'tempel_remove_admin_bar_callback_action'));
        add_action('admin_bar_menu', array($this, 'tempel_admin_bar'), 1);
        add_action('admin_bar_menu', array($this, 'tempel_remove_wp_logo'), 999);
        add_action('wp_enqueue_scripts', array($this, 'tmpl_styles'));
        add_action('admin_enqueue_scripts', array($this, 'tmpl_admin_styles'));
        add_action('login_form', array($this, 'tempel_login_styles'));
    }

    public function tempel_remove_admin_bar_callback_action()
    {
        remove_action('wp_head', '_admin_bar_bump_cb');
    }

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

    function tempel_remove_wp_logo($wp_admin_bar)
    {
        $wp_admin_bar->remove_node('wp-logo');
    }

    public function tmpl_styles()
    {
        wp_enqueue_style('admin-styles', TMPL_PLUGIN_PATH . 'assets/branding/admin-bar.css');
    }

    public function tmpl_admin_styles()
    {
        wp_enqueue_style('admin-styles', TMPL_PLUGIN_PATH . 'assets/branding/admin-theme.css');
        wp_enqueue_Style('support-widget', TMPL_PLUGIN_PATH . 'assets/branding/support-widget.css');
        wp_enqueue_script('admin', TMPL_PLUGIN_PATH . 'assets/branding/admin-script.js', array('jquery'), null, true);
    }

    public function is_wplogin()
    {
        $ABSPATH_MY = str_replace(array('\\', '/'), DIRECTORY_SEPARATOR, ABSPATH);
        return ((in_array($ABSPATH_MY . 'wp-login.php', get_included_files()) || in_array($ABSPATH_MY . 'wp-register.php', get_included_files())) || (isset($_GLOBALS['pagenow']) && $GLOBALS['pagenow'] === 'wp-login.php') || $_SERVER['PHP_SELF'] == '/wp-login.php');
    }

    public function tempel_login_styles()
    {
        if ($this->is_wplogin()) {
            wp_enqueue_style('login-styles', TMPL_PLUGIN_PATH . 'assets/branding/login-screen.css');
            wp_enqueue_script('login', TMPL_PLUGIN_PATH . 'assets/branding/login-script.js', array('jquery'), null, true);
        }
    }
}
