<?php

namespace Tempel;

require_once TEMPEL_SETTINGS_DIR . 'src/abstract/page.php';
require_once TEMPEL_SETTINGS_DIR . 'src/views/partials/settings-navigation.php';

final class Overview_Settings extends Page
{
    public function render(): void
    {
        $backup = Dropbox_Backup::status();
        $monitor = Status_Monitor::get_status();
        $settings = get_option('tmpl_settings', array());
        $settings = is_array($settings) ? $settings : array();
        $backup_state = sanitize_key((string) ($backup['state'] ?? 'idle'));
        $monitor_state = sanitize_key((string) ($monitor['status'] ?? 'neutral'));
        $security_on = ($settings['security_lock'] ?? '') === 'on';
        ?>
        <div class="tmpl_settings__wrap">
            <div class="tmpl_settings__page" id="tempel-overview-settings">
                <div class="tmpl_settings__inner">
                    <?php settings_navigation(); ?>
                    <main class="settings__body">
                        <div class="body__inner tempel-ui-page">
                            <header class="tempel-ui-page-header">
                                <div>
                                    <h1><?php esc_html_e('Overzicht', 'tempel-settings'); ?></h1>
                                    <p><?php esc_html_e('De belangrijkste status van deze website, zonder technische details.', 'tempel-settings'); ?></p>
                                </div>
                            </header>

                            <section class="tempel-ui-grid" aria-label="<?php esc_attr_e('Websitestatus', 'tempel-settings'); ?>">
                                <?php $this->status_card(__('Beveiliging', 'tempel-settings'), $security_on ? __('Actief', 'tempel-settings') : __('Aandacht nodig', 'tempel-settings'), $security_on ? __('Plugininstallaties en nieuwe beheerders zijn geblokkeerd.', 'tempel-settings') : __('De beveiligingsblokkade staat uit.', 'tempel-settings'), $security_on ? 'success' : 'warning'); ?>
                                <?php $this->status_card(__('Back-up', 'tempel-settings'), $this->backup_label($backup_state), (string) ($backup['message'] ?? __('Nog geen back-up uitgevoerd.', 'tempel-settings')), $this->normalize_status($backup_state)); ?>
                                <?php $this->status_card(__('Websitecontrole', 'tempel-settings'), $monitor_state === 'success' ? __('In orde', 'tempel-settings') : ($monitor ? __('Aandacht nodig', 'tempel-settings') : __('Nog niet gecontroleerd', 'tempel-settings')), (string) ($monitor['message'] ?? __('Voer de eerste controle uit via Logboek.', 'tempel-settings')), $this->normalize_status($monitor_state)); ?>
                            </section>

                        </div>
                    </main>
                </div>
            </div>
        </div>
        <?php
    }

    private function status_card(string $label, string $value, string $message, string $status): void
    {
        ?>
        <article class="tempel-ui-card tempel-ui-status tempel-ui-status--<?php echo esc_attr($status); ?>">
            <div>
                <span class="tempel-ui-status__label"><?php echo esc_html($label); ?></span>
                <strong class="tempel-ui-status__value"><?php echo esc_html($value); ?></strong>
                <span class="tempel-ui-status__meta"><?php echo esc_html($message); ?></span>
            </div>
        </article>
        <?php
    }

    private function normalize_status(string $status): string
    {
        return in_array($status, array('success', 'error', 'warning', 'running'), true) ? $status : (in_array($status, array('queued', 'uploading'), true) ? 'running' : 'neutral');
    }

    private function backup_label(string $state): string
    {
        return array(
            'success' => __('Geslaagd', 'tempel-settings'),
            'error' => __('Mislukt', 'tempel-settings'),
            'queued' => __('In wachtrij', 'tempel-settings'),
            'running' => __('Wordt gemaakt', 'tempel-settings'),
            'uploading' => __('Wordt verstuurd', 'tempel-settings'),
        )[$state] ?? __('Nog geen back-up', 'tempel-settings');
    }
}
