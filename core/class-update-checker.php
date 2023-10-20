<?php

namespace Tempel\UpdateChecker;

require plugin_dir_path(__DIR__) . 'vendor/autoload.php';
use YahnisElsts\PluginUpdateChecker\v5\PucFactory;

class UpdateChecker
{

    public function __construct()
    {
        $this->tempel_update_checker();
    }

    public function tempel_update_checker()
    {
        $tempelUpdateChecker = PucFactory::buildUpdateChecker(
            'https://studiotempel.nl/tempel-settings/info.json',
            plugin_dir_path(__DIR__) . 'tempel.php', //Full path to the main plugin file or functions.php.
            'tempel-settings'
        );
    }
}
