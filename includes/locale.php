<?php

namespace Tempel;

class Locale
{
    public function __construct()
    {
        load_plugin_textdomain('tempel-settings', false, TMPL_PLUGIN_LANG_PATH);
    }
}