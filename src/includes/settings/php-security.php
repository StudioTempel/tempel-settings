<?php

namespace Tempel;

if (!defined('ABSPATH')) { exit; }

/** Warns administrators when PHP lacks the October 2026 critical RCE fixes. */
final class PHP_Security
{
    public function __construct()
    {
        add_action('admin_notices', array($this, 'notice'));
    }

    public static function is_patched(string $version = PHP_VERSION): bool
    {
        foreach (array('8.5' => '8.5.12', '8.4' => '8.4.27', '8.3' => '8.3.36', '8.2' => '8.2.35') as $branch => $minimum) {
            if (str_starts_with($version, $branch . '.')) { return version_compare($version, $minimum, '>='); }
        }
        return version_compare($version, '8.6.0', '>=');
    }

    public function notice(): void
    {
        if (!current_user_can('manage_options') || self::is_patched()) { return; }
        echo '<div class="notice notice-error"><p><strong>' . esc_html__('Kritieke PHP-beveiligingsupdate nodig', 'tempel-settings') . '</strong><br>'
            . esc_html(sprintf(__('Deze server gebruikt PHP %s. Laat de hosting PHP direct bijwerken naar minimaal 8.2.35, 8.3.36, 8.4.27 of 8.5.12. Deze kwetsbaarheid kan niet vanuit WordPress worden gerepareerd.', 'tempel-settings'), PHP_VERSION))
            . '</p></div>';
    }
}
