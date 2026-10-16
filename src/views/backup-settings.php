<?php

namespace Tempel;

require_once TEMPEL_SETTINGS_DIR . 'src/abstract/page.php';
require_once TEMPEL_SETTINGS_DIR . 'src/views/partials/settings-navigation.php';

class Backup_Settings extends Page
{
    public function render()
    {
        $settings = Dropbox_Backup::settings();
        $status = Dropbox_Backup::status();
        $connected = Dropbox_Backup::is_connected();
        $next = wp_next_scheduled(Dropbox_Backup::CRON_HOOK);
        $state = sanitize_key((string) ($status['state'] ?? 'idle'));
        $state_class = in_array($state, array('success', 'connected'), true) ? 'success' : (in_array($state, array('error', 'disconnected'), true) ? 'error' : 'neutral');
        $state_label = $this->status_label($state);
        ?>
        <div class="tmpl_settings__wrap">
            <div class="tmpl_settings__page" id="tmpl_backup_settings">
                <div class="tmpl_settings__inner">
                    <?php settings_navigation(); ?>
                    <div class="settings__body">
                        <div class="body__inner">
                            <?php $this->render_notice(); ?>

                            <div class="tempel-backup-hero">
                                <div class="tempel-backup-hero__copy">
                                    <span class="tempel-backup-eyebrow"><?php esc_html_e('Automatische back-ups', 'tempel-settings'); ?></span>
                                    <h1><?php esc_html_e('Back-ups naar Dropbox', 'tempel-settings'); ?></h1>
                                    <p><?php esc_html_e('Bewaar de database en alle WordPress-bestanden automatisch buiten de server.', 'tempel-settings'); ?></p>
                                </div>
                                <div class="tempel-backup-hero__action">
                                    <?php if ($connected) : ?>
                                        <form action="<?php echo esc_url(admin_url('admin-post.php')); ?>" method="post">
                                            <input type="hidden" name="action" value="tempel_dropbox_backup_now">
                                            <?php wp_nonce_field('tempel_dropbox_backup_now'); ?>
                                            <button class="button button-primary"><?php esc_html_e('Nu back-up maken', 'tempel-settings'); ?></button>
                                        </form>
                                    <?php else : ?>
                                        <span class="tempel-backup-hero__hint"><?php esc_html_e('Koppel eerst Dropbox om te beginnen.', 'tempel-settings'); ?></span>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <div class="tempel-backup-overview">
                                <div class="tempel-backup-card tempel-backup-card--<?php echo esc_attr($connected ? 'success' : 'neutral'); ?>">
                                    <span class="tempel-backup-card__label"><?php esc_html_e('Dropbox', 'tempel-settings'); ?></span>
                                    <strong><?php echo esc_html($connected ? __('Gekoppeld', 'tempel-settings') : __('Niet gekoppeld', 'tempel-settings')); ?></strong>
                                    <span><?php echo esc_html($connected ? __('Klaar om back-ups te ontvangen', 'tempel-settings') : __('Koppeling vereist', 'tempel-settings')); ?></span>
                                </div>
                                <div class="tempel-backup-card tempel-backup-card--<?php echo esc_attr($state_class); ?>">
                                    <span class="tempel-backup-card__label"><?php esc_html_e('Laatste resultaat', 'tempel-settings'); ?></span>
                                    <strong><?php echo esc_html($state_label); ?></strong>
                                    <span><?php echo esc_html(!empty($status['timestamp']) ? wp_date('d-m-Y H:i', (int) $status['timestamp']) : __('Nog niet uitgevoerd', 'tempel-settings')); ?></span>
                                </div>
                                <div class="tempel-backup-card tempel-backup-card--neutral">
                                    <span class="tempel-backup-card__label"><?php esc_html_e('Volgende back-up', 'tempel-settings'); ?></span>
                                    <strong><?php echo esc_html($next ? wp_date('d-m-Y', $next) : __('Niet gepland', 'tempel-settings')); ?></strong>
                                    <span><?php echo esc_html($next ? wp_date('H:i', $next) : __('Schakel automatisch back-uppen in', 'tempel-settings')); ?></span>
                                </div>
                            </div>

                            <?php if (!empty($status['message'])) : ?>
                                <div class="tempel-backup-last-message tempel-backup-last-message--<?php echo esc_attr($state_class); ?>">
                                    <span class="tempel-backup-last-message__dot"></span>
                                    <span><?php echo esc_html((string) $status['message']); ?></span>
                                    <?php if (!empty($status['file'])) : ?><code><?php echo esc_html((string) $status['file']); ?></code><?php endif; ?>
                                </div>
                            <?php endif; ?>

                            <form action="options.php" method="post">
                                <?php settings_fields('tempel_backup_settings'); ?>

                                <div class="settings__category">
                                    <div class="category__header"><div class="category__label__wrap">
                                        <div class="category__title"><?php esc_html_e('Planning', 'tempel-settings'); ?></div>
                                        <p><?php esc_html_e('Plan de back-up op een rustig moment voor de server.', 'tempel-settings'); ?></p>
                                    </div></div>
                                    <div class="category__content">
                                        <?php $this->checkbox('enabled', __('Automatische back-ups', 'tempel-settings'), __('Start volgens de planning zodra Dropbox is gekoppeld.', 'tempel-settings'), $settings); ?>
                                        <div class="tempel-backup-field-grid">
                                            <?php $this->select('frequency', __('Frequentie', 'tempel-settings'), array('weekly' => __('Wekelijks', 'tempel-settings'), 'monthly' => __('Iedere 30 dagen', 'tempel-settings')), $settings); ?>
                                            <?php $this->time('time', __('Tijdstip', 'tempel-settings'), __('WordPress-tijdzone', 'tempel-settings'), $settings); ?>
                                            <?php $this->number('retention', __('Back-ups bewaren', 'tempel-settings'), 1, 24, $settings, __('Aantal exemplaren', 'tempel-settings')); ?>
                                        </div>
                                        <?php $this->folder_picker($settings, $connected); ?>
                                    </div>
                                </div>

                                <div class="settings__category">
                                    <div class="category__header"><div class="category__label__wrap">
                                        <div class="category__title"><?php esc_html_e('E-mailmeldingen', 'tempel-settings'); ?></div>
                                        <p><?php esc_html_e('Een mislukte back-up wordt altijd gemeld.', 'tempel-settings'); ?></p>
                                    </div></div>
                                    <div class="category__content">
                                        <?php $this->email('notification_email', __('Meldingen sturen naar', 'tempel-settings'), __('Voor fouten en optionele succesmeldingen.', 'tempel-settings'), $settings); ?>
                                        <?php $this->checkbox('notify_success', __('Ook mailen als het gelukt is', 'tempel-settings'), __('Laat uit om alleen waarschuwingen te ontvangen.', 'tempel-settings'), $settings); ?>
                                    </div>
                                </div>

                                <div class="settings__category tempel-backup-connection">
                                    <div class="category__header"><div class="category__label__wrap">
                                        <div class="category__title"><?php esc_html_e('Dropbox-koppeling', 'tempel-settings'); ?></div>
                                        <p><?php echo esc_html($connected ? __('De koppeling is actief. Technische gegevens hoef je normaal niet meer te wijzigen.', 'tempel-settings') : __('Vul eenmalig de gegevens van je Dropbox-app in en verbind het account.', 'tempel-settings')); ?></p>
                                    </div></div>
                                    <div class="category__content">
                                        <details class="tempel-backup-technical" <?php echo $connected ? '' : 'open'; ?>>
                                            <summary><?php echo esc_html($connected ? __('Technische instellingen tonen', 'tempel-settings') : __('Dropbox-app instellen', 'tempel-settings')); ?></summary>
                                            <div class="tempel-backup-technical__content">
                                                <p class="tempel-backup-help"><?php esc_html_e('Geef de Dropbox-app de rechten files.content.write en files.metadata.read. Voeg daarna onderstaande URL toe bij OAuth 2 → Redirect URIs.', 'tempel-settings'); ?></p>
                                                <?php $this->readonly('redirect_url', __('Redirect-URL', 'tempel-settings'), Dropbox_Backup::callback_url()); ?>
                                                <?php $this->text('app_key', __('App key', 'tempel-settings'), $settings); ?>
                                                <?php $this->password('app_secret', __('App secret', 'tempel-settings'), __('Laat leeg om de opgeslagen sleutel te behouden.', 'tempel-settings')); ?>
                                            </div>
                                        </details>
                                    </div>
                                </div>

                                <div class="settings__form__footer"><div class="form__footer__inner">
                                    <?php submit_button(__('Instellingen opslaan', 'tempel-settings')); ?>
                                </div></div>
                            </form>

                            <div class="tempel-backup-connection-actions">
                                <div>
                                    <strong><?php echo esc_html($connected ? __('Dropbox is gekoppeld', 'tempel-settings') : __('Dropbox is nog niet gekoppeld', 'tempel-settings')); ?></strong>
                                    <span><?php echo esc_html($connected ? __('Back-ups worden per domein in een eigen map geplaatst.', 'tempel-settings') : __('Sla eerst de App key en App secret hierboven op.', 'tempel-settings')); ?></span>
                                </div>
                                <?php if ($connected) : ?>
                                    <form action="<?php echo esc_url(admin_url('admin-post.php')); ?>" method="post">
                                        <input type="hidden" name="action" value="tempel_dropbox_disconnect">
                                        <?php wp_nonce_field('tempel_dropbox_disconnect'); ?>
                                        <button class="button"><?php esc_html_e('Ontkoppelen', 'tempel-settings'); ?></button>
                                    </form>
                                <?php else : ?>
                                    <form action="<?php echo esc_url(admin_url('admin-post.php')); ?>" method="post">
                                        <input type="hidden" name="action" value="tempel_dropbox_connect">
                                        <?php wp_nonce_field('tempel_dropbox_connect'); ?>
                                        <button class="button button-primary"><?php esc_html_e('Verbinden met Dropbox', 'tempel-settings'); ?></button>
                                    </form>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <div class="settings__footer"></div>
                </div>
            </div>
        </div>
        <?php
    }

    private function status_label(string $state): string
    {
        return array(
            'success' => __('Geslaagd', 'tempel-settings'),
            'error' => __('Mislukt', 'tempel-settings'),
            'running' => __('Wordt gemaakt', 'tempel-settings'),
            'uploading' => __('Wordt verstuurd', 'tempel-settings'),
            'queued' => __('In wachtrij', 'tempel-settings'),
            'connected' => __('Klaar', 'tempel-settings'),
            'disconnected' => __('Ontkoppeld', 'tempel-settings'),
        )[$state] ?? __('Nog geen back-up', 'tempel-settings');
    }

    private function render_notice(): void
    {
        $notice = isset($_GET['backup_notice']) ? sanitize_key(wp_unslash($_GET['backup_notice'])) : '';
        $messages = array(
            'connected' => __('Dropbox is gekoppeld.', 'tempel-settings'),
            'disconnected' => __('Dropbox is ontkoppeld.', 'tempel-settings'),
            'queued' => __('De back-up is gestart. Vernieuw deze pagina later om de status te bekijken.', 'tempel-settings'),
            'missing_app' => __('Sla eerst de App key en App secret op.', 'tempel-settings'),
            'oauth_failed' => __('Dropbox koppelen is mislukt. Bekijk de laatste status.', 'tempel-settings'),
            'oauth_state' => __('De Dropbox-koppeling is verlopen. Probeer opnieuw.', 'tempel-settings'),
            'not_connected' => __('Koppel Dropbox voordat je een back-up start.', 'tempel-settings'),
        );
        if (isset($messages[$notice])) {
            $class = in_array($notice, array('connected', 'disconnected', 'queued'), true) ? 'success' : 'error';
            echo '<div class="tempel-settings-notice tempel-settings-notice--' . esc_attr($class) . '">' . esc_html($messages[$notice]) . '</div>';
        }
    }

    private function field_start(string $key, string $label, string $description = ''): void
    {
        ?><div class="settings__field"><div class="settings__field__inner"><div class="settings__label__wrap"><label for="<?php echo esc_attr($key); ?>"><?php echo esc_html($label); ?><?php if ($description !== '') : ?><span class="label__desc"><?php echo esc_html($description); ?></span><?php endif; ?></label></div><div class="settings__input__wrap"><?php
    }

    private function field_end(): void
    {
        ?></div></div></div><?php
    }

    private function checkbox(string $key, string $label, string $description, array $settings): void
    {
        $this->field_start($key, $label, $description); ?>
        <label class="checkbox__switch"><input type="checkbox" name="tempel_dropbox_backup_settings[<?php echo esc_attr($key); ?>]" id="<?php echo esc_attr($key); ?>" <?php checked($settings[$key], 'on'); ?>><span class="checkbox__switch__slider"></span></label>
        <?php $this->field_end();
    }

    private function text(string $key, string $label, array $settings, string $placeholder = ''): void
    {
        $this->field_start($key, $label); ?>
        <input type="text" name="tempel_dropbox_backup_settings[<?php echo esc_attr($key); ?>]" id="<?php echo esc_attr($key); ?>" value="<?php echo esc_attr((string) $settings[$key]); ?>" placeholder="<?php echo esc_attr($placeholder); ?>">
        <?php $this->field_end();
    }

    private function readonly(string $key, string $label, string $value): void
    {
        $this->field_start($key, $label, __('Klik om te kopiëren.', 'tempel-settings')); ?>
        <input type="text" id="<?php echo esc_attr($key); ?>" readonly value="<?php echo esc_attr($value); ?>" onclick="this.select()">
        <?php $this->field_end();
    }

    private function password(string $key, string $label, string $description): void
    {
        $this->field_start($key, $label, $description); ?>
        <input type="password" autocomplete="new-password" name="tempel_dropbox_backup_settings[<?php echo esc_attr($key); ?>]" id="<?php echo esc_attr($key); ?>" value="">
        <?php $this->field_end();
    }

    private function number(string $key, string $label, int $min, int $max, array $settings, string $description = ''): void
    {
        $this->field_start($key, $label, $description); ?>
        <input type="number" min="<?php echo esc_attr((string) $min); ?>" max="<?php echo esc_attr((string) $max); ?>" name="tempel_dropbox_backup_settings[<?php echo esc_attr($key); ?>]" id="<?php echo esc_attr($key); ?>" value="<?php echo esc_attr((string) $settings[$key]); ?>">
        <?php $this->field_end();
    }

    private function select(string $key, string $label, array $options, array $settings): void
    {
        $this->field_start($key, $label); ?>
        <select name="tempel_dropbox_backup_settings[<?php echo esc_attr($key); ?>]" id="<?php echo esc_attr($key); ?>"><?php foreach ($options as $value => $option) : ?><option value="<?php echo esc_attr($value); ?>" <?php selected($settings[$key], $value); ?>><?php echo esc_html($option); ?></option><?php endforeach; ?></select>
        <?php $this->field_end();
    }

    private function time(string $key, string $label, string $description, array $settings): void
    {
        $this->field_start($key, $label, $description); ?>
        <input type="time" name="tempel_dropbox_backup_settings[<?php echo esc_attr($key); ?>]" id="<?php echo esc_attr($key); ?>" value="<?php echo esc_attr((string) $settings[$key]); ?>">
        <?php $this->field_end();
    }

    private function email(string $key, string $label, string $description, array $settings): void
    {
        $this->field_start($key, $label, $description); ?>
        <input type="email" name="tempel_dropbox_backup_settings[<?php echo esc_attr($key); ?>]" id="<?php echo esc_attr($key); ?>" value="<?php echo esc_attr((string) $settings[$key]); ?>">
        <?php $this->field_end();
    }

    private function folder_picker(array $settings, bool $connected): void
    {
        $this->field_start('folder', __('Map in Dropbox', 'tempel-settings'), __('Voor iedere website wordt hierin automatisch een map met de domeinnaam gemaakt.', 'tempel-settings')); ?>
        <div class="tempel-dropbox-folder-field">
            <input type="text" name="tempel_dropbox_backup_settings[folder]" id="folder" value="<?php echo esc_attr((string) $settings['folder']); ?>" placeholder="/Tempel Backups">
            <button type="button" class="button" id="tempel-dropbox-browse" <?php disabled(!$connected); ?>><?php esc_html_e('Map kiezen', 'tempel-settings'); ?></button>
        </div>
        <?php if (!$connected) : ?><span class="tempel-dropbox-folder-note"><?php esc_html_e('Deze knop wordt beschikbaar nadat Dropbox is gekoppeld.', 'tempel-settings'); ?></span><?php endif; ?>
        <div class="tempel-dropbox-browser" id="tempel-dropbox-browser" hidden>
            <div class="tempel-dropbox-browser__header">
                <button type="button" class="button" id="tempel-dropbox-up" aria-label="<?php esc_attr_e('Eén map omhoog', 'tempel-settings'); ?>">&larr;</button>
                <strong id="tempel-dropbox-current-path">/</strong>
                <button type="button" class="button button-primary" id="tempel-dropbox-select"><?php esc_html_e('Deze map gebruiken', 'tempel-settings'); ?></button>
            </div>
            <div class="tempel-dropbox-browser__message" id="tempel-dropbox-browser-message" role="status"></div>
            <div class="tempel-dropbox-browser__folders" id="tempel-dropbox-folders"></div>
        </div>
        <?php $this->field_end();
    }

    public function enqueue_scripts()
    {
        $page = isset($_GET['page']) ? sanitize_key(wp_unslash($_GET['page'])) : '';
        if ($page !== 'tempel-backup-settings') {
            return;
        }

        wp_enqueue_script(
            'tempel-dropbox-backup',
            TEMPEL_SETTINGS_ASSET_URL . 'js/dropbox-backup.js',
            array(),
            filemtime(TEMPEL_SETTINGS_ASSET_DIR . 'js/dropbox-backup.js'),
            true
        );
        wp_localize_script('tempel-dropbox-backup', 'tempelDropboxBrowser', array(
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('tempel_dropbox_folder_browser'),
            'labels' => array(
                'loading' => __('Mappen laden…', 'tempel-settings'),
                'empty' => __('Deze map bevat geen andere mappen.', 'tempel-settings'),
                'error' => __('De Dropbox-mappen konden niet worden geladen.', 'tempel-settings'),
            ),
        ));
    }
}
