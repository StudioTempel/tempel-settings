<?php

namespace Tempel\Abstracts;

abstract class Page
{
    protected string $page_title;
    
    protected string $menu_title;
    
    protected string $capability;
    
    protected string $menu_slug;
    
    protected string $icon_url;
    
    protected int $position;
    
    public function __construct($page_title, $menu_title, $menu_slug, $icon_url, $position, $parent_slug = '', $is_submenu = false, $capability = "manage_options", $render = true)
    {
        $this->menu_slug = $menu_slug;
        $this->page_title = $page_title;
        $this->menu_title = $menu_title;
        $this->capability = $capability;
        $this->icon_url = $icon_url;
        $this->position = $position;

        if ($is_submenu === true && $parent_slug) {
            add_submenu_page(
                $parent_slug,                               // Parent slug
                $page_title,                                // Page title
                $menu_title,                                // Menu title
                $capability,                                // Capability
                $menu_slug,                                 // Menu slug
                $render ? array($this, 'render') : null,    // Callback
                $position                                   // Position
            );
        } else {
            add_menu_page(
                $page_title,                                // Page title
                $menu_title,                                // Menu title
                $capability,                                // Capability
                $menu_slug,                                 // Menu slug
                $render ? array($this, 'render') : null,    // Callback
                $icon_url,                                  // Icon URL
                $position                                   // Position
            );
        }

        add_action('admin_enqueue_scripts', array($this, 'enqueue_scripts'));
    }
    
    public function render()
    {
        ?>
        <div class="wrap">
            <h1>HTML rendered on page</h1>
        </div>
        <?php
    }
    
    public function enqueue_scripts()
    {
    
    }
}