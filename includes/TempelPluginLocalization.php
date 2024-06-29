<?php

namespace Tempel;

class TempelPluginLocalization
{
    public function __construct()
    {
        add_action('plugins_loaded', array($this, 'load_textdomain'));
    }

    public function load_textdomain()
    {
        load_plugin_textdomain('tempel-settings', false, dirname(plugin_basename(__FILE__)) . '/languages/');
    }
}