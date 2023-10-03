<?php

namespace Tempel\Admin;

abstract class Page
{

    protected $slug;

    protected $menu_title;

    protected $capability;

    protected $icon_url;

    function __construct($slug, $page_title, $menu_title, $capability, $icon_url, $position)
    {
        $this->slug = $slug;

        add_menu_page(
            $page_title,
            $menu_title,
            $capability,
            $slug,
            array($this, 'render'),
            $icon_url,
            $position
        );
    }
}
