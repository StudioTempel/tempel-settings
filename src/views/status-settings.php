<?php

namespace Tempel;

require_once TEMPEL_SETTINGS_DIR . 'src/abstract/page.php';
require_once TEMPEL_SETTINGS_DIR . 'src/views/partials/settings-navigation.php';

class Status_Settings extends Page
{
    public function render()
    {
        ?>
        <div class="tmpl_settings__wrap">
            <div class="tmpl_settings__page" id="tmpl_status_settings">
                <div class="tmpl_settings__inner">
                    <?php settings_navigation(); ?>
                    <div class="settings__body">
                        <div class="body__inner">
                            <div class="settings__category">
                                <div class="category__header">
                                    <div class="category__label__wrap">
                                        <div class="category__title">
                                            <?php esc_html_e('Status', 'tempel-settings'); ?>
                                        </div>
                                    </div>
                                </div>
                                <div class="category__content">
                                    <div id="tempel_status_health_setting" class="settings__field tempel-status-overview">
                                        <div class="settings__field__inner">
                                            <div class="settings__input__wrap">
                                                <div class="tempel-health-status">
                                                    <?php foreach ($this->get_health_status() as $key => $status) : ?>
                                                        <div class="tempel-health-status__item tempel-health-status__item--<?php echo esc_attr($status['status']); ?>" data-tempel-status="<?php echo esc_attr($key); ?>">
                                                            <span class="tempel-health-status__dot" aria-hidden="true"></span>
                                                            <span class="tempel-health-status__label"><?php echo esc_html($status['label']); ?></span>
                                                            <span class="tempel-health-status__message"><?php echo esc_html($status['message']); ?></span>
                                                        </div>
                                                    <?php endforeach; ?>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="settings__footer"></div>
                </div>
            </div>
        </div>
        <?php
    }

    private function get_health_status(): array
    {
        if (!class_exists('Tempel\GF_BAG_Address')) {
            return array();
        }

        return GF_BAG_Address::get_health_status();
    }
}
