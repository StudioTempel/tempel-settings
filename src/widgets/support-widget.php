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
            echo $widget->get_error_message();
        }
    }
    
    public function widget_markup()
    {
        
        $faq_items = get_faq_items();
        ?>
        <script>
            jQuery(document).ready(function ($) {
                $('.widget__content__dropdown').on('click', function () {
                    $(this).toggleClass('active');
                    $(this).find('.item__dropdown__value').slideToggle();
                });
            });
        </script>
        <?php widget_header($this->widget_id, $this->title, $this->type, $this->color); ?>
                <div class="widget__content">
                    <div class="widget__content_inner">
                        <?php if ($faq_items): ?>
                            <?php foreach ($faq_items as $faq_item):
                                $title = $faq_item['title'];
                                $content = $faq_item['content'];
                                ?>
                                <div class="widget__content__item widget__content__dropdown">
                                    <div class="item__dropdown__label"><?= $title; ?></div>
                                    <div class="item__dropdown__value"><?= $content; ?></div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <p><?php _e('No FAQ items found', 'tempel-settings'); ?></p>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="widget__footer">
                    <a href="<?= get_faq_link(); ?>" rel="nofollow" target="_blank"><?php _e('View all', 'tempel-settings'); ?></a>
                    <a href="https://studiotempel.nl/contact" target="_blank" rel="nofollow"
                       class="widget__button widget__button__yellow"><?php _e('Ask a question', 'tempel-settings'); ?></a>
                </div>
        <?php widget_footer(); ?>
        <?php
    }
}