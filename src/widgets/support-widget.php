<?php

namespace Tempel;

require_once TEMPEL_SETTINGS_DIR . 'src/abstract/widget.php';

require_once TEMPEL_SETTINGS_DIR . 'src/includes/widget-support-helper-functions.php';

require_once 'partials/widget-header.php';
require_once 'partials/widget-footer.php';

class Support_Widget extends Widget
{
    public function __construct()
    {
        parent::__construct();
        $this->widget_id = 'widget-support';
        $this->color = 'white';
        $this->title = 'Support';
        $this->type = 'support';
    }
    
    public function render_widget()
    {
        $widget = $this->widget_markup();
        if (is_wp_error($widget)) {
            echo esc_html($widget->get_error_message());
        }
    }
    
    public function widget_markup()
    {
        ?>
        <?php widget_header($this->widget_id, $this->title, $this->type, $this->color); ?>
                <div class="widget__content">
                    <div class="widget__content_inner">
                        <div class="widget__content__item">
                            <button type="button" class="item__link tempel-support-action" data-tempel-support-action="clear-cache">
                                <span class="item__label"><?php _e('Clear cache', 'tempel-settings'); ?></span>
                                <span class="item__value" data-tempel-support-action-status></span>
                            </button>
                        </div>
                        <div class="widget__content__item">
                            <button type="button" class="item__link tempel-support-action" data-tempel-support-action="send-test-mail">
                                <span class="item__label"><?php _e('Send test email', 'tempel-settings'); ?></span>
                                <span class="item__value" data-tempel-support-action-status></span>
                            </button>
                        </div>
                    </div>
                </div>
                <div class="widget__footer">
                    <span data-tempel-support-message></span>
                    <a href="<?= esc_url(get_support_ticket_link()); ?>" target="_blank" rel="nofollow noopener"
                       class="widget__button widget__button__yellow"><?php _e('Ask a question', 'tempel-settings'); ?></a>
                </div>
        <?php widget_footer(); ?>
        <?php
    }
}
