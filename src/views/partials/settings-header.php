<?php

namespace Tempel;

require_once TEMPEL_SETTINGS_DIR . 'src/admin.php';

function settings_header()
{
    ?>
    <div class="settings__header">
        <div class="header__inner">
            <div class="header__title">
                <?php page_title(); ?>
            </div>
            <div class="header__nav">
                <div class="nav__inner">
                    <?php
                    $pages = get_admin_pages();
                    menu_loop($pages);
                    ?>
                </div>
            </div>
        </div>
    </div>
    <?php
}

function menu_loop($pages)
{
    foreach ($pages as $page) {
        ?>
        <?php
        $current_page = isset($_GET['page']) ? sanitize_key(wp_unslash($_GET['page'])) : '';
        $menu_slug = sanitize_key($page->menu_slug);
        ?>
        <a href="<?php echo esc_url(admin_url('admin.php?page=' . $menu_slug)); ?>"
           class="nav__item <?php echo esc_attr($menu_slug === $current_page ? 'active' : ''); ?>">
            <?php echo esc_html($page->menu_title); ?>
        </a>
        <?php
    }
}

function page_title() {
    $pages = get_admin_pages();
    foreach ($pages as $page) {
        $current_page = isset($_GET['page']) ? sanitize_key(wp_unslash($_GET['page'])) : '';

        if ($page->menu_slug === $current_page) {
            echo esc_html($page->page_title);
        }
    }
}

function get_admin_pages() {
    $admin = new Admin();
    $pages = $admin->get_pages();
    return $pages;
}
