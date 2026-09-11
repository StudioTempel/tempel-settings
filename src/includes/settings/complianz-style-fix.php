<?php

namespace Tempel;

class Complianz_Style_Fix
{
    public function __construct()
    {
        add_action('wp_enqueue_scripts', array($this, 'enqueue_styles'));
    }

    public function enqueue_styles(): void
    {
        wp_register_style('tempel-complianz-fix', false, array(), TEMPEL_SETTINGS_VERSION);
        wp_enqueue_style('tempel-complianz-fix');
        wp_add_inline_style('tempel-complianz-fix', self::css());
    }

    public static function css(): string
    {
        return '.cmplz-cookiebanner .cmplz-close,.cmplz-cookiebanner .cmplz-close svg{width:20px;height:20px;max-width:20px;max-height:20px}.cmplz-cookiebanner .cmplz-close svg{display:block}';
    }
}
