<?php

namespace Tempel;

class TempelPluginLocalization
{
    public function load_textdomain() : void
    {
        load_plugin_textdomain('tempel-settings', false, dirname(plugin_basename(__FILE__)) . '/languages/');
    }
}