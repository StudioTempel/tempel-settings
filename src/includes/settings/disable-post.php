<?php

/**
 * Disable Default Post Type
 *
 * @since 1.0.0
 */

namespace Tempel;

class Disable_Post
{
    /**
     * Constructor
     */
    function __construct()
    {
        add_action('admin_bar_menu', array($this, 'remove_post_from_admin_bar'), 999);
        add_action('admin_init', array($this, 'disable_draft_new_post_widget'));
        add_action('admin_menu', array($this, 'remove_post_menu_page'));
        add_filter('register_post_type_args', array($this, 'remove_default_post_type'), 999, 2);
        add_action('init', array($this, 'unregister_post'), 999);
    }
    
    function remove_default_post_type($args, $post_type)
    {
        if ( 'post' === $post_type ) {
            $args['public']              = false;
            $args['show_ui']             = false;
            $args['show_in_menu']        = false;
            $args['show_in_admin_bar']   = false;
            $args['show_in_nav_menus']   = false;
            $args['can_export']          = false;
            $args['has_archive']         = false;
            $args['exclude_from_search'] = true;
            $args['publicly_queryable']  = false;
            $args['show_in_rest']        = false;
        }
        return $args;
    }
    
    /**
     * Make the post type non-public, then unregister it
     *
     * @since 1.0.0
     */
    function unregister_post()
    {
        unregister_post_type('post');
    }
    
    /**
     * Make the post uneditable
     *
     * @since 1.0.0
     */
    function remove_post_menu_page()
    {
        remove_menu_page('edit.php', 'post');
    }
    
    /**
     * Remove new post from admin bar
     *
     * @since 1.0.0
     */
    function remove_post_from_admin_bar()
    {
        global $wp_admin_bar;
        $wp_admin_bar->remove_node('new-post');
        $wp_admin_bar->remove_node('new-content');
    }
    
    /**
     * Remove the draft widget from the dashboard
     *
     * @since 1.0.0
     */
    function disable_draft_new_post_widget()
    {
        remove_meta_box('dashboard_quick_press', 'dashboard', 'side');
    }
}
