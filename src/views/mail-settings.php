<?php

namespace Tempel;

require_once TEMPEL_SETTINGS_DIR . 'src/abstract/page.php';
require_once TEMPEL_SETTINGS_DIR . 'src/views/partials/settings-navigation.php';

class Mail_Settings extends Page
{
    public function render()
    {
        $draft = $this->get_draft();
        ?>
        <div class="tmpl_settings__wrap">
            <div class="tmpl_settings__page" id="tmpl_mail_settings">
                <div class="tmpl_settings__inner">
                    <?php settings_navigation(); ?>
                    <div class="settings__body">
                        <div class="body__inner">
                            <?php $this->render_notice(); ?>

                            <form action="<?php echo esc_url(admin_url('admin-post.php')); ?>" method="post">
                                <input type="hidden" name="action" value="tempel_send_user_mail">
                                <?php wp_nonce_field('tempel_send_user_mail', 'tempel_send_user_mail_nonce'); ?>

                                <div class="settings__category">
                                    <div class="category__header">
                                        <div class="category__label__wrap">
                                            <div class="category__title">
                                                <?php esc_html_e('Mail', 'tempel-settings'); ?>
                                            </div>
                                            <div class="category__description">
                                                <?php esc_html_e('Verstuur een HTML-mail vanuit de website naar geselecteerde gebruikers.', 'tempel-settings'); ?>
                                            </div>
                                            <div class="tempel-mail-tags tempel-mail-tags--header">
                                                <?php foreach ($this->get_available_tags() as $tag => $description) : ?>
                                                    <span class="tempel-mail-tag" title="<?php echo esc_attr($description); ?>"><?php echo esc_html($tag); ?></span>
                                                <?php endforeach; ?>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="category__content">
                                        <div class="settings__field">
                                            <div class="settings__field__inner">
                                                <div class="settings__label__wrap">
                                                    <label for="tempel_mail_recipients">
                                                        <?php esc_html_e('Ontvangers', 'tempel-settings'); ?>
                                                        <span class="label__desc"><?php esc_html_e('Selecteer een of meerdere WordPress-gebruikers.', 'tempel-settings'); ?></span>
                                                    </label>
                                                </div>
                                                <div class="settings__input__wrap">
                                                    <select id="tempel_mail_recipients" name="tempel_mail_recipients[]" multiple>
                                                        <?php foreach ($this->get_users() as $user) : ?>
                                                            <option value="<?php echo esc_attr($user->ID); ?>" <?php selected(in_array((int) $user->ID, $draft['recipients'], true)); ?>>
                                                                <?php echo esc_html(sprintf('%1$s (%2$s)', $user->display_name, $user->user_email)); ?>
                                                            </option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="settings__field">
                                            <div class="settings__field__inner">
                                                <div class="settings__label__wrap">
                                                    <label for="tempel_mail_subject">
                                                        <?php esc_html_e('Onderwerp', 'tempel-settings'); ?>
                                                    </label>
                                                </div>
                                                <div class="settings__input__wrap">
                                                    <input type="text" id="tempel_mail_subject" name="tempel_mail_subject" value="<?php echo esc_attr($draft['subject']); ?>" required>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="settings__field tempel-mail-editor-field">
                                            <div class="settings__field__inner">
                                                <div class="settings__label__wrap">
                                                    <label for="tempel_mail_message">
                                                        <?php esc_html_e('Bericht', 'tempel-settings'); ?>
                                                        <span class="label__desc"><?php esc_html_e('Deze inhoud wordt als HTML-mail verstuurd.', 'tempel-settings'); ?></span>
                                                    </label>
                                                </div>
                                                <div class="settings__input__wrap">
                                                    <?php
                                                    wp_editor(
                                                        $draft['message'],
                                                        'tempel_mail_message',
                                                        array(
                                                            'textarea_name' => 'tempel_mail_message',
                                                            'textarea_rows' => 14,
                                                            'media_buttons' => false,
                                                            'teeny' => false,
                                                            'quicktags' => true,
                                                        )
                                                    );
                                                    ?>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="settings__form__footer">
                                    <div class="form__footer__inner">
                                        <?php submit_button(__('Mail versturen', 'tempel-settings')); ?>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                    <div class="settings__footer"></div>
                </div>
            </div>
        </div>
        <?php
    }

    public function enqueue_scripts()
    {
        $screen = get_current_screen();

        if (!$screen || $screen->id !== 'tempel-settings_page_tempel-mail-settings') {
            return;
        }

        wp_enqueue_editor();
        wp_enqueue_style('tmpl-settings-select2', TEMPEL_SETTINGS_ASSET_URL . 'vendor/select2.min.css');
        wp_enqueue_script('tmpl-settings-select2', TEMPEL_SETTINGS_ASSET_URL . 'vendor/select2.full.min.js', array('jquery'), null, true);
    }

    private function get_users(): array
    {
        return get_users(array(
            'orderby' => 'display_name',
            'order' => 'ASC',
            'fields' => array('ID', 'display_name', 'user_email'),
        ));
    }

    private function get_draft(): array
    {
        $draft = get_option('tmpl_mail_settings', array());

        if (!is_array($draft)) {
            $draft = array();
        }

        return array(
            'recipients' => isset($draft['recipients']) && is_array($draft['recipients'])
                ? array_values(array_filter(array_map('absint', $draft['recipients'])))
                : array(),
            'subject' => isset($draft['subject']) ? (string) $draft['subject'] : '',
            'message' => isset($draft['message']) ? (string) $draft['message'] : '',
        );
    }

    private function get_available_tags(): array
    {
        return array(
            '[naam]' => __('Volledige naam of weergavenaam van de gebruiker.', 'tempel-settings'),
            '[voornaam]' => __('Voornaam van de gebruiker.', 'tempel-settings'),
            '[achternaam]' => __('Achternaam van de gebruiker.', 'tempel-settings'),
            '[email]' => __('E-mailadres van de gebruiker.', 'tempel-settings'),
            '[website_url]' => __('URL van de website.', 'tempel-settings'),
            '[website url]' => __('URL van de website.', 'tempel-settings'),
            '[website_naam]' => __('Naam van de website.', 'tempel-settings'),
        );
    }

    private function render_notice(): void
    {
        if (isset($_GET['tempel_mail_error']) && sanitize_key(wp_unslash($_GET['tempel_mail_error'])) === 'missing') {
            ?>
            <div class="tempel-settings-notice tempel-settings-notice--error">
                <?php esc_html_e('Selecteer ontvangers en vul een onderwerp en bericht in.', 'tempel-settings'); ?>
            </div>
            <?php
            return;
        }

        if (!isset($_GET['tempel_mail_sent'])) {
            return;
        }

        $sent = absint($_GET['tempel_mail_sent']);
        $failed = isset($_GET['tempel_mail_failed']) ? absint($_GET['tempel_mail_failed']) : 0;
        ?>
        <div class="tempel-settings-notice <?php echo esc_attr($failed > 0 ? 'tempel-settings-notice--warning' : 'tempel-settings-notice--success'); ?>">
            <?php
            echo esc_html(sprintf(
                __('Mail verzonden naar %1$d gebruiker(s). Mislukt: %2$d.', 'tempel-settings'),
                $sent,
                $failed
            ));
            ?>
        </div>
        <?php
    }
}
