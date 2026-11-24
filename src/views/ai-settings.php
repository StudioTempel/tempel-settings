<?php

namespace Tempel;

require_once TEMPEL_SETTINGS_DIR . 'src/abstract/page.php';
require_once TEMPEL_SETTINGS_DIR . 'src/views/partials/settings-navigation.php';

final class AI_Settings extends Page
{
    public function render(): void
    {
        $settings = get_option(AI_Assistant::SETTINGS_OPTION, array());
        $settings = is_array($settings) ? $settings : array();
        $hub = Hub_Client::settings();
        $hub_connected = Hub_Client::is_connected();
        ?>
        <div class="tmpl_settings__wrap">
            <div class="tmpl_settings__page" id="tmpl_ai_settings">
                <div class="tmpl_settings__inner">
                    <?php settings_navigation(); ?>
                    <div class="settings__body">
                        <div class="body__inner tempel-ui-page">
                            <div class="tempel-ui-page-header">
                                <div>
                                    <h1><?php esc_html_e('AI-instellingen', 'tempel-settings'); ?></h1>
                                    <p><?php esc_html_e('Beheer de technische verbinding, toegestane rollen en verbruikslimieten van Tempel AI.', 'tempel-settings'); ?></p>
                                </div>
                            </div>

                            <?php if (!empty($_GET['tempel_hub_error'])) : ?><div class="tempel-ui-notice tempel-ui-notice--error"><strong><?php esc_html_e('Koppelen mislukt', 'tempel-settings'); ?></strong><p><?php echo esc_html(sanitize_text_field(wp_unslash($_GET['tempel_hub_error']))); ?></p></div><?php endif; ?>

                            <section class="tempel-ui-card">
                                <h2><?php esc_html_e('Tempel Hub', 'tempel-settings'); ?></h2>
                                <?php if ($hub_connected) : ?>
                                    <p><strong><?php esc_html_e('Centraal verbonden', 'tempel-settings'); ?></strong><br><?php esc_html_e('OpenAI-sleutels, Templeton-regels en verbruikslimieten worden centraal beheerd. De API-sleutel staat niet op deze website.', 'tempel-settings'); ?></p>
                                    <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>"><?php wp_nonce_field('tempel_hub_disconnect'); ?><input type="hidden" name="action" value="tempel_hub_disconnect"><button class="button" type="submit"><?php esc_html_e('Tempel Hub ontkoppelen', 'tempel-settings'); ?></button></form>
                                <?php else : ?>
                                    <p><?php esc_html_e('Deze website probeert na de update automatisch te koppelen wanneer dit in Tempel CRM is toegestaan. Gebruik alleen bij een mislukte automatische controle eventueel een handmatige koppelcode.', 'tempel-settings'); ?></p>
                                    <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>"><?php wp_nonce_field('tempel_hub_pair'); ?><input type="hidden" name="action" value="tempel_hub_pair">
                                        <div class="settings__field"><div class="settings__field__inner"><div class="settings__label__wrap"><label for="tempel_hub_url"><?php esc_html_e('Hub-adres', 'tempel-settings'); ?></label></div><div class="settings__input__wrap"><input id="tempel_hub_url" name="hub_url" type="url" value="<?php echo esc_attr($hub['hub_url']); ?>" required></div></div></div>
                                        <div class="settings__field"><div class="settings__field__inner"><div class="settings__label__wrap"><label for="tempel_pairing_code"><?php esc_html_e('Koppelcode', 'tempel-settings'); ?></label></div><div class="settings__input__wrap"><input id="tempel_pairing_code" name="pairing_code" type="text" autocomplete="one-time-code" placeholder="ABCD-1234" required></div></div></div>
                                        <button class="button button-primary" type="submit"><?php esc_html_e('Verbinden met Tempel Hub', 'tempel-settings'); ?></button>
                                    </form>
                                <?php endif; ?>
                            </section>

                            <form action="options.php" method="post">
                                <?php settings_fields('tempel_ai_settings'); ?>

                                <section class="tempel-ui-card">
                                    <h2><?php esc_html_e('Toegang', 'tempel-settings'); ?></h2>
                                    <p class="tempel-ui-field__help"><?php esc_html_e('Kies lokaal welke WordPress-rollen de centraal beheerde AI-functies mogen gebruiken.', 'tempel-settings'); ?></p>
                                    <?php $this->roles($settings); ?>
                                </section>

                                <div class="settings__form__footer"><div class="form__footer__inner">
                                    <?php submit_button(__('Instellingen opslaan', 'tempel-settings')); ?>
                                </div></div>
                            </form>
                        </div>
                    </div>
                    <div class="settings__footer"></div>
                </div>
            </div>
        </div>
        <?php
    }

    private function toggle(string $key, string $label, string $help, array $settings): void
    {
        ?>
        <div class="settings__field"><div class="settings__field__inner">
            <div class="settings__label__wrap"><label for="tempel_ai_<?php echo esc_attr($key); ?>"><?php echo esc_html($label); ?><span class="label__desc"><?php echo esc_html($help); ?></span></label></div>
            <div class="settings__input__wrap"><input type="hidden" name="<?php echo esc_attr(AI_Assistant::SETTINGS_OPTION); ?>[<?php echo esc_attr($key); ?>]" value=""><label class="checkbox__switch" for="tempel_ai_<?php echo esc_attr($key); ?>"><input type="checkbox" id="tempel_ai_<?php echo esc_attr($key); ?>" name="<?php echo esc_attr(AI_Assistant::SETTINGS_OPTION); ?>[<?php echo esc_attr($key); ?>]" value="on" <?php checked(($settings[$key] ?? '') === 'on'); ?>><span class="checkbox__switch__slider"></span></label></div>
        </div></div>
        <?php
    }

    private function password(string $key, string $label, string $help): void
    {
        ?>
        <div class="settings__field"><div class="settings__field__inner">
            <div class="settings__label__wrap"><label for="tempel_ai_<?php echo esc_attr($key); ?>"><?php echo esc_html($label); ?><span class="label__desc"><?php echo esc_html($help); ?></span></label></div>
            <div class="settings__input__wrap"><input type="password" autocomplete="new-password" id="tempel_ai_<?php echo esc_attr($key); ?>" name="<?php echo esc_attr(AI_Assistant::SETTINGS_OPTION); ?>[<?php echo esc_attr($key); ?>]" value=""></div>
        </div></div>
        <?php
    }

    private function select(string $key, string $label, array $options, string $value): void
    {
        ?>
        <div class="settings__field"><div class="settings__field__inner">
            <div class="settings__label__wrap"><label for="tempel_ai_<?php echo esc_attr($key); ?>"><?php echo esc_html($label); ?></label></div>
            <div class="settings__input__wrap"><select id="tempel_ai_<?php echo esc_attr($key); ?>" name="<?php echo esc_attr(AI_Assistant::SETTINGS_OPTION); ?>[<?php echo esc_attr($key); ?>]"><?php foreach ($options as $option_value => $option_label) : ?><option value="<?php echo esc_attr($option_value); ?>" <?php selected($value, $option_value); ?>><?php echo esc_html($option_label); ?></option><?php endforeach; ?></select></div>
        </div></div>
        <?php
    }

    private function roles(array $settings): void
    {
        $selected = (array) ($settings['roles'] ?? array('administrator', 'editor'));
        ?>
        <div class="settings__field"><div class="settings__field__inner">
            <div class="settings__label__wrap"><span class="tempel-field-label"><?php esc_html_e('Toegestane rollen', 'tempel-settings'); ?></span><span class="label__desc"><?php esc_html_e('WordPress controleert daarnaast altijd of de gebruiker het bericht mag bewerken.', 'tempel-settings'); ?></span></div>
            <div class="settings__input__wrap tempel-ai-role-list">
                <?php foreach (wp_roles()->roles as $role => $details) : ?>
                    <label><input type="checkbox" name="<?php echo esc_attr(AI_Assistant::SETTINGS_OPTION); ?>[roles][]" value="<?php echo esc_attr($role); ?>" <?php checked(in_array($role, $selected, true)); ?>> <?php echo esc_html(translate_user_role($details['name'])); ?></label>
                <?php endforeach; ?>
            </div>
        </div></div>
        <?php
    }

    private function number(string $key, string $label, array $settings, int $default, int $min, int $max): void
    {
        ?>
        <div class="settings__field"><div class="settings__field__inner">
            <div class="settings__label__wrap"><label for="tempel_ai_<?php echo esc_attr($key); ?>"><?php echo esc_html($label); ?></label></div>
            <div class="settings__input__wrap"><input type="number" id="tempel_ai_<?php echo esc_attr($key); ?>" name="<?php echo esc_attr(AI_Assistant::SETTINGS_OPTION); ?>[<?php echo esc_attr($key); ?>]" min="<?php echo esc_attr((string) $min); ?>" max="<?php echo esc_attr((string) $max); ?>" value="<?php echo esc_attr((string) ($settings[$key] ?? $default)); ?>"></div>
        </div></div>
        <?php
    }

}
