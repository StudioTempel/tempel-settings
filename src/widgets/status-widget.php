<?php

namespace Tempel;

require_once TEMPEL_SETTINGS_DIR . 'src/abstract/widget.php';

require_once TEMPEL_SETTINGS_DIR . 'src/includes/widget-status-helper-functions.php';

require_once 'partials/widget-header.php';
require_once 'partials/widget-footer.php';

class Status_Widget extends Widget
{
    public function __construct()
    {
        parent::__construct();
        $this->widget_id = 'widget-status';
        $this->color = 'black';
        $this->title = 'Status';
        $this->type = 'status';
    }
    
    public function render_widget()
    {
        $widget_id = $this->widget_id;
        $color = $this->color;
        $title = $this->title;
        $type = $this->type;
        
        echo $this->widget_markup();
    }
    
    function widget_markup()
    {
        
        ?>
        <?php widget_header($this->widget_id, $this->title, $this->type, $this->color); ?>
        <div class="widget__content">
            <div class="widget__content__inner">
                <div class="widget__content__item">
                    <div class="item__label"><?php _e('Last round of updates', 'tempel-settings') ?></div>
                    <div class="item__value"><?= get_safeupdate_day(); ?></div>
                </div>
                <div class="widget__content__item">
                    <div class="item__label"><?php _e('Last backup', 'tempel-settings'); ?></div>
                    <div class="item__value"><?= get_backup_interval(); ?></div>
                </div>
                <div class="widget__content__item">
                    <?php $last_checkup = get_last_checkup(); ?>
                    <?php if ($last_checkup['error'] && $last_checkup['error'] !== '') : ?>
                        <div class="item__label"><?php _e('Last checkup', 'tempel-settings'); ?></div>
                        <div class="item__value"><?= $last_checkup['error']; ?></div>
                    <?php elseif ($last_checkup['show_link'] === true): ?>
                        <a href="https://studiotempel/contact" target="_blank" class="item__link">
                            <div class="item__label"><?php _e('Last checkup', 'tempel-settings'); ?></div>
                            <div class="item__value item__value--<?= $last_checkup['color']; ?>"><?= $last_checkup['date']; ?></div>
                        </a>
                    <?php else: ?>
                        <div class="item__label"><?php _e('Last checkup', 'tempel-settings'); ?></div>
                        <div class="item__value item__value--<?= $last_checkup['color']; ?>"><?= $last_checkup['date']; ?></div>
                    <?php endif; ?>
                </div>
                <?php if (get_customer_package() && show_service_contract_tier() === true): ?>
                    <div class="widget__content__item">
                        <?php if (service_contract_upgradable() === true): ?>
                        <a
                                rel="nofollow"
                                target="_blank"
                                href="<?= get_service_contract_upgrade_link(); ?>"
                                class="item__link"
                        >
                            <?php endif; ?>
                            <div class="item__label"><?php _e('Servicecontract', 'tempel-settings'); ?></div>
                            <div class="item__value"><?= get_customer_package(); ?></div>
                            <?php if (service_contract_upgradable() === true): ?>
                        </a>
                    <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        <?php widget_footer(); ?>
        <?php
    }
}