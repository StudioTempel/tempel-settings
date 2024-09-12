<?php

namespace Tempel;

abstract class Page
{
    public string $page_title;
    
    public string $menu_title;
    
    public string $menu_slug;
    
    public string $icon_url;
    
    public int $position;
    
    public string $parent_slug;
    
    public bool $is_submenu = false;
    
    public string $capability = "manage_options";
    
    public bool $render = true;
    
    public function __construct($page_title, $menu_title, $menu_slug, $icon_url, $position, $parent_slug = '', $is_submenu = false, $capability = "manage_options", $render = true)
    {
        $this->page_title = $page_title;
        $this->menu_title = $menu_title;
        $this->menu_slug = $menu_slug;
        $this->icon_url = $icon_url;
        $this->position = $position;
        $this->parent_slug = $parent_slug;
        $this->is_submenu = $is_submenu;
        $this->capability = $capability;
        $this->render = $render;

        add_action('admin_menu', [$this, 'add_page']);
        add_action('admin_enqueue_scripts', array($this, 'enqueue_scripts'));
    }
    
    function add_page() {
        if ($this->is_submenu === true && $this->parent_slug) {
            add_submenu_page(
                $this->parent_slug,                               // Parent slug
                $this->page_title,                                // Page title
                $this->menu_title,                                // Menu title
                $this->capability,                                // Capability
                $this->menu_slug,                                 // Menu slug
                $this->render ? array($this, 'render') : null,    // Callback
                $this->position                                   // Position
            );
        } else {
            add_menu_page(
                $this->page_title,                                // Page title
                $this->menu_title,                                // Menu title
                $this->capability,                                // Capability
                $this->menu_slug,                                 // Menu slug
                $this->render ? array($this, 'render') : null,    // Callback
                $this->icon_url,                                  // Icon URL
                $this->position                                   // Position
            );
        }
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