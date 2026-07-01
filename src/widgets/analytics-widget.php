<?php

namespace Tempel;

require_once TEMPEL_SETTINGS_DIR . 'src/abstract/widget.php';

require_once TEMPEL_SETTINGS_DIR . 'src/widgets/partials/widget-header.php';
require_once TEMPEL_SETTINGS_DIR . 'src/widgets/partials/widget-footer.php';

class Analytics_Widget extends Widget
{
    public function __construct()
    {
        parent::__construct();
        $this->widget_id = 'widget-analytics';
        $this->color = 'blue';
        $this->title = __('Visitors', 'tempel-settings');
        $this->type = 'analytics';
    }

    public function render_widget()
    {
        echo $this->widget_markup();
    }

    function widget_markup()
    {
        ?>
        <?php widget_header($this->widget_id, $this->title, $this->type, $this->color); ?>
        <div class="widget__content" data-tempel-analytics-widget data-tempel-analytics-error="<?php esc_attr_e('Visitors could not be retrieved.', 'tempel-settings'); ?>">
            <div class="widget__content__inner">
                <div class="widget__content__item widget__content__item--message" data-tempel-analytics-message>
                    <div class="item__label"><?php _e('Last 7 days', 'tempel-settings'); ?></div>
                    <div class="item__value"></div>
                </div>
            </div>
        </div>
        <?php widget_footer(); ?>
        <?php
    }
}
