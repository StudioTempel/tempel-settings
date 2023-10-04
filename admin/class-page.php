<?php

namespace Tempel\Admin;

abstract class Page
{

    protected $slug;

    protected $menu_title;

    protected $capability;

    protected $icon_url;

    function __construct($slug, $page_title, $menu_title, $icon_url, $position, $render = true, $capability = "manage_options" )
    {

        $this->slug = $slug;

        add_menu_page(
            $page_title,                                // Page title
            $menu_title,                                // Menu title
            $capability,                                // Capability
            $slug,                                      // Menu slug
            $render ? array($this, 'render') : null ,                     // Callback
            $icon_url,                                  // Icon URL
            $position                                   // Position
        );
    }
}
