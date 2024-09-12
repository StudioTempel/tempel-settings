<?php

/**
 * Removes default dashboard widgets
 *
 * @since 1.0.0
 */


namespace Tempel;

class Remove_Dashboard_Widgets
{
    /**
     * Constructor
     */
    function __construct()
    {
        add_action('wp_dashboard_setup', array($this, 'remove_core_widgets'), 999);
        add_action('admin_init', array($this, 'remove_welcome_panel'), 999);
    }
    
    /**
     * Remove dashboard widgets
     *
     * @since 1.0.0
     */
    function remove_core_widgets()
    {
        global $wp_meta_boxes;
        unset($wp_meta_boxes['dashboard']['normal']['core']['dashboard_right_now']);                    // Right Now
        unset($wp_meta_boxes['dashboard']['normal']['core']['dashboard_activity']);                     // Activity
        unset($wp_meta_boxes['dashboard']['normal']['core']['dashboard_recent_comments']);              // Recent Comments
        unset($wp_meta_boxes['dashboard']['normal']['core']['dashboard_incoming_links']);               // Incoming Links
        unset($wp_meta_boxes['dashboard']['normal']['core']['dashboard_plugins']);                      // Plugins
        unset($wp_meta_boxes['dashboard']['side']['core']['dashboard_quick_press']);                    // Quick Press
        unset($wp_meta_boxes['dashboard']['side']['core']['dashboard_recent_drafts']);                  // Recent Drafts
        unset($wp_meta_boxes['dashboard']['side']['core']['dashboard_primary']);                        // WordPress blog
        unset($wp_meta_boxes['dashboard']['side']['core']['dashboard_secondary']);                      // Other WordPress News
        unset($wp_meta_boxes['dashboard']['normal']['core']['dashboard_site_health']);                  // Site Health
    }
    
    function remove_welcome_panel()
    {
        remove_action('welcome_panel', 'wp_welcome_panel');
    }
}
