<?php

namespace Tempel;

class Localization
{
    public function __construct()
    {
        load_plugin_textdomain('tempel-settings', false, TEMPEL_SETTINGS_LANG_DIR);
    }
}