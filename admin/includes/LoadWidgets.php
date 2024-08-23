<?php

namespace Tempel\Admin;

require_once TMPL_PLUGIN_DIR . 'admin/widgets/StatusWidget.php';
require_once TMPL_PLUGIN_DIR . 'admin/widgets/SupportWidget.php';
require_once TMPL_PLUGIN_DIR . 'admin/widgets/ConversionWidget.php';
require_once TMPL_PLUGIN_DIR . 'helpers/Helper.php';

use Tempel\Admin\Widgets\StatusWidget;
use Tempel\Admin\Widgets\SupportWidget;
use Tempel\Admin\Widgets\ConversionWidget;
use Tempel\Helpers\Helper;

class LoadWidgets
{
    public function __construct()
    {
        if (Helper::sanitize_checkbox_value(Helper::return_option('enable_widgets'))) {
            new StatusWidget();
            
            new SupportWidget();
            
            if (class_exists('GFForms')) {
                new ConversionWidget();
            }
        }
    }
}