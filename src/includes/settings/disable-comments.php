<?php

/**
 * Disable comments
 *
 * @since 1.0.0
 */


namespace Tempel\Settings;

class Disable_Comments
{
    
    function __construct()
    {
        add_action('admin_menu', array($this, 'remove_edit_comments_from_menu'));
        add_action('wp_before_admin_bar_render', array($this, 'remove_comments_from_menu'));
        add_action('admin_bar_menu', array($this, 'remove_comments_from_admin_bar'), 999);
        add_action('admin_init', array($this, 'disable_access_to_comments'));
        add_action('wp_dashboard_setup', array($this, 'remove_comments_metabox'));
        add_action('init', array($this, 'remove_comment_support'));
        add_action('admin_init', array($this, 'close_comments_pings'));
        add_action('admin_init', array($this, 'hide_existing_comments'));
        add_action('wp_dashboard_setup', array($this, 'remove_draft_widget'), 999);
        add_action('admin_footer', array($this, 'remove_add_new_post_href_in_admin_bar'));
    }
    
    function remove_add_new_post_href_in_admin_bar()
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
    
    function remove_draft_widget()
    {
        remove_meta_box('dashboard_quick_press', 'dashboard', 'side');
    }
    
    
    function remove_edit_comments_from_menu()
    {
        remove_menu_page('edit-comments.php', 'comments');
    }
    
    function remove_comments_from_admin_bar($wp_admin_bar)
    {
        if (is_admin_bar_showing()) {
            remove_action('admin_bar_menu', 'wp_admin_bar_comments_menu', 60);
        }
    }
    
    function remove_comments_from_menu()
    {
        global $wp_admin_bar;
        $wp_admin_bar->remove_menu('comments');
        $wp_admin_bar->remove_node('comments');
    }
    
    function disable_access_to_comments()
    {
        global $pagenow;
        
        if ($pagenow === 'edit-comments.php') {
            wp_redirect(admin_url());
            exit;
        }
    }
    
    function remove_comments_metabox()
    {
        remove_meta_box('dashboard_recent_comments', 'dashboard', 'normal');
    }
    
    function remove_comment_support()
    {
        if (is_array(get_post_types())) {
            foreach (get_post_types() as $post_type) {
                if (post_type_supports($post_type, 'comments')) {
                    remove_post_type_support($post_type, 'comments');
                    remove_post_type_support($post_type, 'trackbacks');
                }
            }
        } else {
            if (post_type_supports(get_post_types(), 'comments')) {
                remove_post_type_support(get_post_types(), 'comments');
                remove_post_type_support(get_post_types(), 'trackbacks');
            }
        }
    }
    
    function close_comments_pings()
    {
        add_filter('comments_open', '__return_false', 20, 2);
        add_filter('pings_open', '__return_false', 20, 2);
    }
    
    function hide_existing_comments()
    {
        add_filter('comments_array', '__return_empty_array', 10, 2);
    }
}
