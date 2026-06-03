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
            <?php
            $current_page = isset($_GET['page']) ? sanitize_key(wp_unslash($_GET['page'])) : '';
            $menu_slug = sanitize_key($page->menu_slug);
            ?>
            <a href="<?php echo esc_url(admin_url('admin.php?page=' . $menu_slug)); ?>"
               class="nav__item <?php echo esc_attr($menu_slug === $current_page ? 'active' : ''); ?>">
                <?php echo esc_html($page->page_title); ?>
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
