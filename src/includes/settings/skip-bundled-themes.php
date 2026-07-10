<?php

/**
 * Skip bundled WordPress themes
 *
 * @since 2.7.16
 */

namespace Tempel;

class Skip_Bundled_Themes
{
    public function __construct()
    {
        if (!defined('CORE_UPGRADE_SKIP_NEW_BUNDLED')) {
            define('CORE_UPGRADE_SKIP_NEW_BUNDLED', true);
        }
    }
}
