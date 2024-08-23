<?php

/**
 * Disable Default Post Type
 * 
 * @since 1.0.0
 */

namespace Tempel\Public;

class SettingDisableDefaultPT
{
    /**
     * Constructor
     */
    function __construct()
    {
        add_action('admin_menu', array($this, 'make_post_uneditable'));
        add_action('admin_bar_menu', array($this, 'remove_post_from_admin_bar'), 999);
        // add_action('admin_footer', array($this, 'remove_add_new_post_href_in_admin_bar'));
        add_action('init', array($this, 'edit_post_object'));
        add_action('admin_init', array($this, 'disable_draft_new_post_widget'));
    }

    /**
     * Remove the post type from the admin menu
     * 
     * @since 1.0.0
     */
    function make_post_uneditable()
    {
        remove_menu_page('edit.php', 'post', '');
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
     * Remove new post from admin bar
     * 
     * @since 1.0.0
     */
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

    /**
     * Remove the draft widget from the dashboard
     * 
     * @since 1.0.0
     */
    function disable_draft_new_post_widget()
    {
        remove_meta_box('dashboard_quick_press', 'dashboard', 'side');
    }

    /**
     * Edit the post object to make post type non-public and not show in the menu editor
     * 
     * @since 1.0.0
     */
    function edit_post_object()
    {
        $object = get_post_type_object('post');
        $object->public = false;
        $object->show_in_nav_menus = false;
    }
}
