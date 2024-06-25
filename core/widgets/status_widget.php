<?php

namespace Tempel\Core\Widgets;

require_once "widget.php";

class StatusWidget extends Widget
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
        
        $widget = $this->widget_markup();
        
        echo $widget;
    }
    
    function widget_markup()
    {
        
        ?>
        <div class="tmpl_widget widget--<?= $this->type; ?> widget--<?= $this->color; ?>">
            <div class="widget__inner">
                <div class="widget__icon">
                    <?php $this->get_widget_icon(); ?>
                </div>
                <div class="widget__header">
                    <div class="widget__title"><?= $this->title; ?></div>
                </div>
                <div class="widget__content">
                    <div class="widget__content__inner">
                        <div class="widget__content__item">
                            <div class="item__label">Laatste update ronde</div>
                            <div class="item__value"><?= $this->get_last_update(); ?></div>
                        </div>
                        <div class="widget__content__item">
                            <div class="item__label">Laatste backup</div>
                            <div class="item__value"><?= $this->get_backup_interval(); ?></div>
                        </div>
                        <div class="widget__content__item">
                            <?php
                            $last_checkup = $this->get_last_checkup();
                            
                            $last_checkup = \DateTime::createFromFormat('m/Y', $last_checkup);
                            $time_now = new \DateTime("now", new \DateTimeZone('Europe/Amsterdam'));
                            ?>
                            
                            <?php if ($last_checkup->diff($time_now)->days > 30) : ?>
                                <a href="#" class="item__link">
                                    <div class="item__label">Laatste checkup</div>
                                    <div class="item__value item__value--red"><?= $this->get_last_checkup(); ?></div>
                                </a>
                            <?php else: ?>
                                <div class="item__label">Laatste checkup</div>
                                <div class="item__value"><?= $this->get_last_checkup(); ?></div>
                            <?php endif; ?>
                        </div>
                        <?php if ($this->get_customer_package()): ?>
                            <div class="widget__content__item">
                                <a href="#" class="item__link">
                                    <div class="item__label">Pakket</div>
                                    <div class="item__value"><?= $this->get_customer_package(); ?></div>

                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }
    
    public function get_backup_interval(): string
    {
        $option = $this->get_settings('backup-interval-field');
        
        if (is_wp_error($option) || empty($option)) $option = '<span class="tmpl_widget__error">Kon de laatste backup datum niet ophalen</span>';
        
        $time_now = new \DateTime("now", new \DateTimeZone('Europe/Amsterdam'));
        
        $time_now = strtotime($time_now->format('H:i'));
        $time_last_backup = strtotime($option);
        
        if ($time_last_backup > $time_now) {
            $option = __('Yesterday', 'tempel-settings');
        } else if ($time_last_backup == $time_now) {
            $option = __('Now', 'tempel-settings');
        } else {
            $option = __('Today', 'tempel-settings');
        }
        
        
        return $option;
    }
    
    public function get_last_checkup()
    {
        $option = $this->get_settings('last-checkup-date');
        
        if (is_wp_error($option) || empty($option)) $option = '<span class="tmpl_widget__error">Kon de laatste checkup datum niet ophalen</span>';
        
        return $option;
    }
    
    public function get_last_update()
    {
        $option = $this->get_settings('last-update-date');
        
        if (is_wp_error($option) || empty($option)) $option = '<span class="tmpl_widget__error">Kon de laatste update datum niet ophalen</span>';
        
        return $option;
    }
    
    public function get_customer_package(): string
    {
//        $option = $this->get_settings('package-type-field');
//
//        if (is_wp_error($option) || empty($option)) $option = '<span class="tmpl_widget__error">Kon het pakket niet ophalen</span>';
//
//        $option = ucfirst($option);

//        return $option;
        return '';
    }
    
    public function get_settings($option)
    {
        $settings = get_option('tempel-widget-settings-data');
        return $settings[$option];
    }
}