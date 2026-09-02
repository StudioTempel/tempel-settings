<?php

namespace Tempel;

$composer_autoload = plugin_dir_path(__FILE__) . '../vendor/autoload.php';
$puc_loader = plugin_dir_path(__FILE__) . '../vendor/yahnis-elsts/plugin-update-checker/load-v5p4.php';

if (file_exists(plugin_dir_path(__FILE__) . '../vendor/composer/autoload_real.php')) {
    require_once $composer_autoload;
} elseif (file_exists($puc_loader)) {
    require_once $puc_loader;
}

use YahnisElsts\PluginUpdateChecker\v5\PucFactory;

class Updater
{
    public function __construct()
    {
        $this->tempel_update_checker();
    }
    
    public function tempel_update_checker()
    {
        if (!class_exists(PucFactory::class)) {
            return;
        }

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
