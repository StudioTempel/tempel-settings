<?php

namespace Tempel;

/** Makes the classic editor publish box calmer without removing controls. */
final class Compact_Publish_Box
{
    public function __construct()
    {
        add_action('admin_enqueue_scripts', array($this, 'enqueue_assets'));
    }

    public function enqueue_assets(string $hook): void
    {
        if (!in_array($hook, array('post.php', 'post-new.php'), true)) {
            return;
        }

        wp_enqueue_style(
            'tempel-compact-publish-box',
            TEMPEL_SETTINGS_ASSET_URL . 'css/compact-publish-box.css',
            array(),
            filemtime(TEMPEL_SETTINGS_ASSET_DIR . 'css/compact-publish-box.css')
        );
        wp_enqueue_script(
            'tempel-compact-publish-box',
            TEMPEL_SETTINGS_ASSET_URL . 'js/compact-publish-box.js',
            array(),
            filemtime(TEMPEL_SETTINGS_ASSET_DIR . 'js/compact-publish-box.js'),
            true
        );
    }
}
