<?php

namespace Tempel;

require_once TEMPEL_SETTINGS_DIR . 'src/abstract/widget.php';

require_once TEMPEL_SETTINGS_DIR . 'src/includes/widget-support-helper-functions.php';

require_once TEMPEL_SETTINGS_DIR . 'src/widgets/partials/widget-header.php';
require_once TEMPEL_SETTINGS_DIR . 'src/widgets/partials/widget-footer.php';

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
                        <?php if (has_supported_cache_plugin()): ?>
                            <div class="widget__content__item">
                                <a href="#" class="item__link tempel-support-action" data-tempel-support-action="clear-cache">
                                    <span class="item__label"><?php _e('Clear cache', 'tempel-settings'); ?></span>
                                    <span class="item__value"></span>
                                </a>
                            </div>
                        <?php endif; ?>
                        <div class="widget__content__item">
                            <a href="#" class="item__link tempel-support-action" data-tempel-support-action="send-test-mail">
                                <span class="item__label"><?php _e('Send test email', 'tempel-settings'); ?></span>
                                <span class="item__value"></span>
                            </a>
                        </div>
                        <div class="widget__content__item">
                            <a href="<?= esc_url(get_support_ticket_link()); ?>" target="_blank" rel="nofollow noopener" class="item__link">
                                <span class="item__label"><?php _e('Ask a question', 'tempel-settings'); ?></span>
                                <span class="item__value"></span>
                            </a>
                        </div>
                    </div>
                </div>
        <?php widget_footer(); ?>
        <?php
    }
}
