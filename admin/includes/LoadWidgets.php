<?php

namespace Tempel\Admin;

require_once TMPL_PLUGIN_DIR . 'admin/widgets/StatusWidget.php';
require_once TMPL_PLUGIN_DIR . 'admin/widgets/SupportWidget.php';
require_once TMPL_PLUGIN_DIR . 'admin/widgets/ConversionWidget.php';

use Tempel\Admin\Widgets\StatusWidget;
use Tempel\Admin\Widgets\SupportWidget;
use Tempel\Admin\Widgets\ConversionWidget;

class LoadWidgets
{
    public function __construct()
    {
        new StatusWidget();
        
        new SupportWidget();

        if (class_exists('GFForms')) {
            new ConversionWidget();
        }
    }
}