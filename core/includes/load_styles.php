<?php

namespace Tempel\Core;

class LoadPluginStyles
{
    function __construct()
    {
        add_action('admin_enqueue_scripts', array($this, 'load_plugin_styles'));
    }

    function load_plugin_styles()
    {
        if (is_admin()) {
            wp_enqueue_style('tempel-settings-styles', TMPL_PLUGIN_CSS_URL . 'styles.css');
        }
    }
}