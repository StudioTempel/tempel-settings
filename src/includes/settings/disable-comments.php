<?php

/**
 * Disable comments
 *
 * @since 1.0.0
 */


namespace Tempel;

class Disable_Comments
{
    
    function __construct()
    {
        
        add_action('init', array($this, 'remove_comment_support'));
        
        add_action('admin_menu', array($this, 'remove_comments_edit_page'));
        add_action('admin_init', array($this, 'remove_comments_edit_page_redirect'));
        
        add_action('admin_init', array($this, 'disable_comments_dashboard'));
        
        add_action('init', array($this, 'disable_comments_admin_bar'));
        
        add_action('wp_before_admin_bar_render', array($this, 'remove_comments_from_menu'));
        
        add_filter('comments_open', '__return_false', 20, 2);
        add_filter('pings_open', '__return_false', 20, 2);
        add_filter('comments_array', '__return_empty_array', 20, 2);
    }
    
    /**
     * Removes comment support from all post types
     */
    function remove_comment_support()
    {
        if (is_array(get_post_types())) {
            foreach (get_post_types() as $post_type) {
                if (post_type_supports($post_type, 'comments')) {
                    remove_post_type_support($post_type, 'comments');
                    remove_post_type_support($post_type, 'trackbacks');
                }
            }
        }
    }
    
    /**
     * Removes access to the comments edit page
     */
    function remove_comments_edit_page()
    {
        remove_menu_page('edit-comments.php', 'comments');
    }
    
    /**
     * Redirects any user trying to access the comments edit page
     */
    function remove_comments_edit_page_redirect()
    {
        global $pagenow;
        if($pagenow === 'edit-comments.php') {
            wp_redirect(admin_url());
            exit;
        }
    }
    
    /**
     * Removes the comments dashboard widget
     */
    function disable_comments_dashboard()
    {
        remove_meta_box('dashboard_recent_comments', 'dashboard', 'normal');
    }
    
    /**
     * Removes the edit comments link from the admin bar
     */
    function disable_comments_admin_bar()
    {
        if(is_admin_bar_showing()) {
            remove_action('admin_bar_menu', 'wp_admin_bar_comments_menu', 60);
        }
    }
    
    /**
     * Removes the unread comments link from the admin bar
     */
    function remove_comments_from_menu()
    {
        global $wp_admin_bar;
        $wp_admin_bar->remove_menu('comments');
        $wp_admin_bar->remove_node('comments');
    }
}
