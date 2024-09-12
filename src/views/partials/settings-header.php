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
        <a href="/wp-admin/admin.php?page=<?php echo $page->menu_slug; ?>"
           class="nav__item <?php echo $page->menu_slug === $_GET['page'] ? 'active' : ''; ?>">
            <?php echo $page->menu_title; ?>
        </a>
        <?php
    }
}

function page_title() {
    $pages = get_admin_pages();
    foreach ($pages as $page) {
        if ($page->menu_slug === $_GET['page']) {
            echo __($page->page_title, 'tempel-settings');
        }
    }
}

function get_admin_pages() {
    $admin = new Admin();
    $pages = $admin->get_pages();
    return $pages;
}