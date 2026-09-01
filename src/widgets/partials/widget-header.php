<?php

namespace Tempel;

require_once TEMPEL_SETTINGS_DIR . 'src/includes/helper-functions.php';
require_once TEMPEL_SETTINGS_DIR . 'src/abstract/widget.php';

function widget_header($widget_id, $title, $type, $color, $total_value = null)
{
    ?>
<div class="tmpl_widget widget--<?= esc_attr(sanitize_html_class($type)); ?> widget--<?= esc_attr(sanitize_html_class($color)); ?>">
    <div class="widget__inner">
    <div class="widget__icon">
        <?php Widget::get_widget_icon(); ?>
    </div>
    <?php if ($widget_id === 'widget-conversion'):
        $total_submissions = get_total_submissions();
        ?>
        <div class="widget__total_conversion_value"><?= esc_html($total_submissions); ?></div>
    <?php elseif ($widget_id === 'widget-analytics'): ?>
        <div class="widget__total_conversion_value" data-tempel-analytics-visitors>...</div>
    <?php endif; ?>
    <div class="widget__header">
        
        <?php if ($widget_id === 'widget-conversion'):
            $total_submissions = get_total_submissions();
            ?>
            <div class="widget__title">
                <?php if ($total_submissions === 1) {
                    _e('Conversion', 'tempel-settings');
                } else {
                    _e('Conversions', 'tempel-settings');
                } ?>
            </div>
        <?php else: ?>
            <div class="widget__title"><?= esc_html($title); ?></div>
        <?php endif; ?>
        
    <?php if ($widget_id === 'widget-conversion'): ?>
            <div class="widget__scope"><?php echo esc_html(sprintf(__('Last %d days', 'tempel-settings'), get_conversion_period_days())); ?></div>
        <?php endif; ?>
    </div>
    <?php
}
