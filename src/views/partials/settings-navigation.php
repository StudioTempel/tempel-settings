<?php

namespace Tempel;

require_once TEMPEL_SETTINGS_DIR . 'src/admin.php';

function settings_navigation()
{
    ?>
    <div class="settings__sidebar">
        <div class="sidebar__inner">
            <div class="sidebar__header">
                <div class="sidebar__title">
                    <?php esc_html_e('Settings', 'tempel-settings'); ?>
                </div>
            </div>
            <div class="sidebar__nav">
                <div class="nav__inner">
                    <?php
                    menu_loop(get_admin_pages());
                    ?>
                </div>
            </div>
        </div>
    </div>
    
    <?php
}

function menu_loop($pages)
{
    $groups = array(
        __('Overzicht', 'tempel-settings') => array('tempel-settings'),
        __('Instellingen', 'tempel-settings') => array('tempel-general-settings', 'tempel-widget-settings', 'tempel-gform-address-settings', 'tempel-performance-settings', 'tempel-mail-settings'),
        __('Gereedschap', 'tempel-settings') => array('tempel-backup-settings', 'tempel-stager-settings'),
        __('Logboek', 'tempel-settings') => array('tempel-status-settings'),
    );

    echo '<ul>';
    $current_page = isset($_GET['page']) ? sanitize_key(wp_unslash($_GET['page'])) : '';
    foreach ($groups as $label => $slugs) {
        $available = array_intersect_key($pages, array_flip($slugs));
        if (!$available) continue;
        echo '<li class="tempel-nav-group">';
        echo '<span class="tempel-nav-group__label">' . esc_html($label) . '</span>';
        echo '<ul>';
        foreach ($available as $page) {
            $menu_slug = sanitize_key($page->menu_slug);
            echo '<li><a href="' . esc_url(admin_url('admin.php?page=' . $menu_slug)) . '" class="nav__item ' . esc_attr($menu_slug === $current_page ? 'active' : '') . '">' . esc_html($page->page_title) . '</a></li>';
        }
        echo '</ul></li>';
    }
    echo '</ul>';
}

function get_admin_pages()
{
    return Admin::registered_pages();
}
