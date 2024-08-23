<?php

namespace Tempel\Admin\Widgets;

require_once TMPL_PLUGIN_DIR . 'src/abstract/Widget.php';
use Tempel\Abstracts\Widget;

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
                            <div class="item__value"><?= $this->getSafeupdateDay(); ?></div>
                        </div>
                        <div class="widget__content__item">
                            <div class="item__label">Laatste backup</div>
                            <div class="item__value"><?= $this->get_backup_interval(); ?></div>
                        </div>
                        <div class="widget__content__item">
                            <?php $last_checkup = $this->get_last_checkup(); ?>
                            <?php if ($last_checkup['error'] && $last_checkup['error'] !== '') : ?>
                                <div class="item__label">Laatste checkup</div>
                                <div class="item__value"><?= $last_checkup['error']; ?></div>
                            <?php elseif ($last_checkup['show_link'] === true): ?>
                                <a href="#" class="item__link">
                                    <div class="item__label">Laatste checkup</div>
                                    <div class="item__value item__value--red"><?= $last_checkup['date']; ?></div>
                                </a>
                            <?php else: ?>
                                <div class="item__label">Laatste checkup</div>
                                <div class="item__value"><?= $last_checkup['date']; ?></div>
                            <?php endif; ?>
                        </div>
                        <?php if ($this->get_customer_package() && $this->showServiceContractTier() === true): ?>
                            <div class="widget__content__item">
                                <?php if ($this->serviceContractUpgradable() === true): ?>
                                <a
                                    rel="nofollow"
                                    target="_blank"
                                    href="<?= $this->getServiceContractUpgradeLink(); ?>"
                                    class="item__link"
                                >
                                    <?php endif; ?>
                                    <div class="item__label">Onderhoudscontract</div>
                                    <div class="item__value"><?= $this->get_customer_package(); ?></div>
                                    <?php if ($this->serviceContractUpgradable() === true): ?>
                                </a>
                            <?php endif; ?>
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
        $option = $this->get_settings('status_backup_interval');
        
        if (is_wp_error($option) || empty($option)) return '<span class="tmpl_widget__error">' . __('Kon de laatste backup datum niet ophalen', 'tempel-settings') . '</span>';
        
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
    
    public function get_last_checkup() : array
    {
        $lastCheckup = [
            'date' => '',
            'show_link' => false,
            'error' => ''
        ];
        $option = $this->get_settings('status_last_checkup_date');
        
        if (is_wp_error($option) || empty($option)) return ['error' => '<span class="tmpl_widget__error">' . __('Kon de laatste checkup datum niet ophalen', 'tempel-settings') . '</span>'];
        
        $lastCheckup['date'] = $option;
        
        $dateLastCheckup = \DateTime::createFromFormat('m/Y', $lastCheckup['date']);
        $now = new \DateTime("now", new \DateTimeZone('Europe/Amsterdam'));
        
        if ($dateLastCheckup->diff($now)->days > 30) {
            $lastCheckup['show_link'] = true;
        }
        
        return $lastCheckup;
    }
    
    private function getSafeupdateDay(): string
    {
        $option = $this->get_settings('status_safeupdate_day');
        
        if (is_wp_error($option) || empty($option)) return '<span class="tmpl_widget__error">' . __('Could not retrieve last update date', 'tempel-settings') . '</span>';
        
        $now = new \DateTime("now", new \DateTimeZone('Europe/Amsterdam'));
        $lastUpdate = $now->modify('last ' . $option);
        return $lastUpdate->format('d/m');
    }
    
    
    // Service contract
    private function showServiceContractTier(): bool
    {
        $option = $this->get_settings('status_show_service_contract_tier');
        
        if (is_wp_error($option) || empty($option)) return false;
        
        return true;
    }
    
    private function serviceContractUpgradable(): bool
    {
        $option = $this->get_settings('status_service_contract_upgradable');
        
        if (is_wp_error($option) || empty($option)) return false;
        
        return true;
    }
    
    public function get_customer_package(): string
    {
        $option = $this->get_settings('status_service_contract_tier');
        
        if (is_wp_error($option) || empty($option)) return '<span class="tmpl_widget__error">' . __('Kon het pakket niet ophalen', 'tempel-settings') . '</span>';
        
        $option = ucfirst($option);
        
        return $option;
    }
    
    private function getServiceContractUpgradeLink() : string
    {
        $option = $this->get_settings('status_service_contract_upgrade_link');
        
        if (is_wp_error($option) || empty($option)) return '';
        
        return $option;
    }
    
    public function get_settings($option)
    {
        $settings = get_option('tmpl_widget_settings');
        return $settings[$option] ?? false;
    }
}