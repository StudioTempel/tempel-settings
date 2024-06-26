<?php

/**
 * Removes default dashboard widgets
 * 
 * @since 1.0.0
 */


namespace Tempel\Public;

class SettingRemoveDashboardWidgets
{
    /**
     * Constructor
     */
    function __construct()
    {
        add_action('wp_dashboard_setup', array($this, 'remove_dashboard_widgets'));
    }

    /**
     * Remove dashboard widgets
     * 
     * @since 1.0.0
     */
    function remove_dashboard_widgets()
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
    }
}
