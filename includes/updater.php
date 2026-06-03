<?php

namespace Tempel;

require plugin_dir_path(__FILE__) . '../vendor/autoload.php';

use YahnisElsts\PluginUpdateChecker\v5\PucFactory;

class Updater
{
    public function __construct()
    {
        $this->tempel_update_checker();
    }
    
    public function tempel_update_checker()
    {
        $tempelUpdateChecker = PucFactory::buildUpdateChecker(
            'https://github.com/StudioTempel/tempel-settings',
            plugin_dir_path(__DIR__) . 'tempel.php',
            'tempel-settings'
        );

        $tempelUpdateChecker->setBranch('main');

        if (defined('TEMPEL_SETTINGS_GITHUB_TOKEN') && TEMPEL_SETTINGS_GITHUB_TOKEN) {
            $tempelUpdateChecker->setAuthentication(TEMPEL_SETTINGS_GITHUB_TOKEN);
        }
        
        $tempelUpdateChecker->getVcsApi()->enableReleaseAssets();
    }
}
