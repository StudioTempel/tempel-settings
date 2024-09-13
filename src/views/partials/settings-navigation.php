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
                    <?php echo __('Settings', 'tempel-settings'); ?>
                </div>
            </div>
            <div class="sidebar__nav">
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
    echo '<ul>';
    foreach ($pages as $page) {
        ?>
        <li>
            <a href="/wp-admin/admin.php?page=<?php echo $page->menu_slug; ?>"
               class="nav__item <?php echo $page->menu_slug === $_GET['page'] ? 'active' : ''; ?>">
                <?php echo $page->page_title; ?>
            </a>
        </li>
        <?php
    }
    echo '</ul>';
}

function get_admin_pages()
{
    $admin = new Admin();
    $pages = $admin->get_pages();
    return $pages;
}