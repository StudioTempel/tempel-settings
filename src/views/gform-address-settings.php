<?php

namespace Tempel;

require_once TEMPEL_SETTINGS_DIR . 'src/abstract/page.php';
require_once TEMPEL_SETTINGS_DIR . 'src/views/partials/settings-navigation.php';

class Gform_Address_Settings extends Page
{
    public function render()
    {
        ?>
        <div class="tmpl_settings__wrap">
            <div class="tmpl_settings__page" id="tmpl_gform_address_settings">
                <div class="tmpl_settings__inner">
                    <?php settings_navigation(); ?>
                    <div class="settings__body">
                        <div class="body__inner">
                            <?php if (isset($_GET['tempel_cleanup_done'])) : ?>
                                <?php $deleted = isset($_GET['tempel_cleanup_deleted']) ? absint($_GET['tempel_cleanup_deleted']) : 0; ?>
                                <div class="notice notice-success inline"><p><?php echo esc_html(sprintf(_n('%d oude formulierinzending is permanent verwijderd.', '%d oude formulierinzendingen zijn permanent verwijderd.', $deleted, 'tempel-settings'), $deleted)); ?></p></div>
                            <?php endif; ?>
                            <form action="options.php" method="post">
                                <?php settings_fields('tempel_settings'); ?>
                                <?php wp_nonce_field(Form_Entry_Retention::MANUAL_ACTION, 'tempel_cleanup_nonce'); ?>

                                <div class="settings__category">
                                    <div class="category__header">
                                        <div class="category__label__wrap">
                                            <div class="category__title">
                                                <?php esc_html_e('Gform adres veld', 'tempel-settings'); ?>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="category__content">

                                        <div id="gf_bag_address_enabled_setting" class="settings__field">
                                            <div class="settings__field__inner">
                                                <div class="settings__label__wrap">
                                                    <label for="gf_bag_address_enabled">
                                                        <?php esc_html_e('Toon Gform adres veld', 'tempel-settings'); ?>
                                                    </label>
                                                </div>
                                                <div class="settings__input__wrap">
                                                    <label for="gf_bag_address_enabled" class="checkbox__switch">
                                                        <input
                                                                type="checkbox"
                                                                name="tmpl_settings[gf_bag_address_enabled]"
                                                                id="gf_bag_address_enabled"
                                                            <?php echo $this->is_checked('gf_bag_address_enabled'); ?>
                                                        >
                                                        <span class="checkbox__switch__slider"></span>
                                                    </label>
                                                </div>
                                            </div>
                                        </div>

                                        <div id="gf_bag_address_api_key_setting" class="settings__field">
                                            <div class="settings__field__inner">
                                                <div class="settings__label__wrap">
                                                    <label for="gf_bag_address_api_key">
                                                        <?php esc_html_e('PostcodeAPI.nu API-sleutel', 'tempel-settings'); ?>
                                                        <?php if ($this->get_masked_postcode_api_key() !== '') : ?>
                                                            <span class="label__desc"><?php echo esc_html(sprintf(__('Opgeslagen: %s', 'tempel-settings'), $this->get_masked_postcode_api_key())); ?></span>
                                                        <?php endif; ?>
                                                    </label>
                                                </div>
                                                <div class="settings__input__wrap">
                                                    <input
                                                            type="password"
                                                            name="tmpl_settings[gf_bag_address_api_key]"
                                                            id="gf_bag_address_api_key"
                                                            class="settings-input-code"
                                                            value="<?php echo esc_attr($this->get_value('gf_bag_address_api_key')); ?>"
                                                            placeholder="<?php esc_attr_e('Vul je API-sleutel in', 'tempel-settings'); ?>"
                                                            autocomplete="off"
                                                    >
                                                </div>
                                            </div>
                                        </div>

                                        <div id="gf_bag_address_endpoint_setting" class="settings__field">
                                            <div class="settings__field__inner">
                                                <div class="settings__label__wrap">
                                                    <label for="gf_bag_address_endpoint">
                                                        <?php esc_html_e('PostcodeAPI.nu endpoint', 'tempel-settings'); ?>
                                                    </label>
                                                </div>
                                                <div class="settings__input__wrap">
                                                    <input
                                                            type="url"
                                                            name="tmpl_settings[gf_bag_address_endpoint]"
                                                            id="gf_bag_address_endpoint"
                                                            class="settings-input-code"
                                                            value="<?php echo esc_attr($this->get_postcode_api_endpoint_value()); ?>"
                                                    >
                                                </div>
                                            </div>
                                        </div>

                                        <div id="gf_bag_address_test_setting" class="settings__field">
                                            <div class="settings__field__inner">
                                                <div class="settings__label__wrap">
                                                    <label>
                                                        <?php esc_html_e('Test API verbinding', 'tempel-settings'); ?>
                                                        <span class="label__desc"><?php esc_html_e('Doet een echte test met de opgeslagen key en endpoint.', 'tempel-settings'); ?></span>
                                                    </label>
                                                </div>
                                                <div class="settings__input__wrap">
                                                    <div class="tempel-api-test">
                                                        <div class="tempel-api-test__fields">
                                                            <input type="text" id="tempel_postcode_test_postcode" value="6545CA" aria-label="<?php esc_attr_e('Test postcode', 'tempel-settings'); ?>">
                                                            <input type="text" id="tempel_postcode_test_huisnummer" value="29" aria-label="<?php esc_attr_e('Test huisnummer', 'tempel-settings'); ?>">
                                                            <button type="button" class="button button-primary" id="tempel_test_postcode_api">
                                                                <?php esc_html_e('Test API verbinding', 'tempel-settings'); ?>
                                                            </button>
                                                        </div>
                                                        <div id="tempel_postcode_api_test_result" class="tempel-api-test__result" role="status" aria-live="polite"></div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div id="gf_bag_address_timeout_setting" class="settings__field">
                                            <div class="settings__field__inner">
                                                <div class="settings__label__wrap">
                                                    <label for="gf_bag_address_timeout">
                                                        <?php esc_html_e('PostcodeAPI.nu time-out (seconden)', 'tempel-settings'); ?>
                                                    </label>
                                                </div>
                                                <div class="settings__input__wrap">
                                                    <input
                                                            type="number"
                                                            min="1"
                                                            max="30"
                                                            name="tmpl_settings[gf_bag_address_timeout]"
                                                            id="gf_bag_address_timeout"
                                                            value="<?php echo esc_attr($this->get_value('gf_bag_address_timeout', '8')); ?>"
                                                    >
                                                </div>
                                            </div>
                                        </div>

                                        <div id="gf_bag_address_monthly_limit_setting" class="settings__field">
                                            <div class="settings__field__inner">
                                                <div class="settings__label__wrap">
                                                    <label for="gf_bag_address_monthly_limit">
                                                        <?php esc_html_e('PostcodeAPI.nu maandlimiet', 'tempel-settings'); ?>
                                                        <span class="label__desc"><?php echo esc_html($this->get_postcode_api_usage_label()); ?></span>
                                                    </label>
                                                </div>
                                                <div class="settings__input__wrap">
                                                    <input
                                                            type="number"
                                                            min="0"
                                                            max="1000000"
                                                            name="tmpl_settings[gf_bag_address_monthly_limit]"
                                                            id="gf_bag_address_monthly_limit"
                                                            value="<?php echo esc_attr($this->get_value('gf_bag_address_monthly_limit', '1000')); ?>"
                                                    >
                                                </div>
                                            </div>
                                        </div>

                                        <div id="gf_bag_address_cache_days_setting" class="settings__field">
                                            <div class="settings__field__inner">
                                                <div class="settings__label__wrap">
                                                    <label for="gf_bag_address_cache_days">
                                                        <?php esc_html_e('Adres-cache bewaren (dagen)', 'tempel-settings'); ?>
                                                        <span class="label__desc"><?php esc_html_e('Herhaalde aanvragen voor dezelfde postcode en huisnummer gebruiken de cache en tellen niet mee als API-call.', 'tempel-settings'); ?></span>
                                                    </label>
                                                </div>
                                                <div class="settings__input__wrap">
                                                    <input
                                                            type="number"
                                                            min="0"
                                                            max="365"
                                                            name="tmpl_settings[gf_bag_address_cache_days]"
                                                            id="gf_bag_address_cache_days"
                                                            value="<?php echo esc_attr($this->get_value('gf_bag_address_cache_days', '30')); ?>"
                                                    >
                                                </div>
                                            </div>
                                        </div>

                                        <div id="gf_bag_address_rate_limit_setting" class="settings__field">
                                            <div class="settings__field__inner">
                                                <div class="settings__label__wrap">
                                                    <label for="gf_bag_address_rate_limit">
                                                        <?php esc_html_e('Max aanvragen per minuut', 'tempel-settings'); ?>
                                                        <span class="label__desc"><?php esc_html_e('Per ingelogde gebruiker of IP-adres. Dit voorkomt pieken en misbruik.', 'tempel-settings'); ?></span>
                                                    </label>
                                                </div>
                                                <div class="settings__input__wrap">
                                                    <input
                                                            type="number"
                                                            min="1"
                                                            max="300"
                                                            name="tmpl_settings[gf_bag_address_rate_limit]"
                                                            id="gf_bag_address_rate_limit"
                                                            value="<?php echo esc_attr($this->get_value('gf_bag_address_rate_limit', '20')); ?>"
                                                    >
                                                </div>
                                            </div>
                                        </div>

                                    </div>
                                </div>

                                <div class="settings__category">
                                    <div class="category__header"><div class="category__label__wrap"><div class="category__title"><?php esc_html_e('Antispam', 'tempel-settings'); ?></div></div></div>
                                    <div class="category__content">
                                        <div class="settings__field"><div class="settings__field__inner">
                                            <div class="settings__label__wrap"><label for="gf_antispam_enabled"><?php esc_html_e('Onzichtbare antispam voor alle formulieren', 'tempel-settings'); ?></label><p class="description"><?php esc_html_e('Schakelt de Gravity Forms-honeypot globaal in en markeert inzendingen zonder JavaScript of met een onnatuurlijk korte invultijd als spam. Bezoekers zien geen captcha.', 'tempel-settings'); ?></p></div>
                                            <div class="settings__input__wrap"><input type="hidden" name="tmpl_settings[gf_antispam_enabled]" value=""><label class="checkbox__switch" for="gf_antispam_enabled"><input type="checkbox" name="tmpl_settings[gf_antispam_enabled]" id="gf_antispam_enabled" <?php echo $this->is_checked('gf_antispam_enabled'); ?>><span class="checkbox__switch__slider"></span></label></div>
                                        </div></div>
                                        <div class="settings__field"><div class="settings__field__inner">
                                            <div class="settings__label__wrap"><label for="gf_antispam_min_seconds"><?php esc_html_e('Minimale invultijd (seconden)', 'tempel-settings'); ?></label><p class="description"><?php esc_html_e('Inzendingen die sneller worden verstuurd gaan naar spam. Drie seconden is een veilige standaard.', 'tempel-settings'); ?></p></div>
                                            <div class="settings__input__wrap"><input type="number" min="1" max="30" name="tmpl_settings[gf_antispam_min_seconds]" id="gf_antispam_min_seconds" value="<?php echo esc_attr($this->get_value('gf_antispam_min_seconds', '3')); ?>"></div>
                                        </div></div>
                                    </div>
                                </div>

                                <div class="settings__category">
                                    <div class="category__header"><div class="category__label__wrap"><div class="category__title"><?php esc_html_e('Bewaartermijn en opschonen', 'tempel-settings'); ?></div></div></div>
                                    <div class="category__content">
                                        <div class="settings__field"><div class="settings__field__inner">
                                            <div class="settings__label__wrap"><label for="form_entry_retention_enabled"><?php esc_html_e('Bewaartermijn inschakelen', 'tempel-settings'); ?></label><p class="description"><?php esc_html_e('Verwijdert ieder uur maximaal 500 inzendingen ouder dan de gekozen termijn, inclusief spam en prullenbak.', 'tempel-settings'); ?></p></div>
                                            <div class="settings__input__wrap"><input type="hidden" name="tmpl_settings[form_entry_retention_enabled]" value=""><label class="checkbox__switch" for="form_entry_retention_enabled"><input type="checkbox" name="tmpl_settings[form_entry_retention_enabled]" id="form_entry_retention_enabled" <?php echo $this->is_checked('form_entry_retention_enabled'); ?>><span class="checkbox__switch__slider"></span></label></div>
                                        </div></div>
                                        <div class="settings__field"><div class="settings__field__inner">
                                            <div class="settings__label__wrap"><label for="form_entry_retention_days"><?php esc_html_e('Inzendingen bewaren (dagen)', 'tempel-settings'); ?></label><p class="description"><?php esc_html_e('Het conversiedashboard gebruikt deze termijn wanneer deze korter is dan 30 dagen.', 'tempel-settings'); ?></p></div>
                                            <div class="settings__input__wrap"><input type="number" min="1" max="3650" name="tmpl_settings[form_entry_retention_days]" id="form_entry_retention_days" value="<?php echo esc_attr($this->get_value('form_entry_retention_days', '365')); ?>"></div>
                                        </div></div>
                                        <div class="settings__field"><div class="settings__field__inner">
                                            <div class="settings__label__wrap"><label><?php esc_html_e('Oude inzendingen nu opschonen', 'tempel-settings'); ?></label><p class="description"><?php esc_html_e('Verwijdert direct maximaal 500 oude inzendingen permanent. Deze actie kan niet ongedaan worden gemaakt.', 'tempel-settings'); ?></p></div>
                                            <div class="settings__input__wrap"><button type="submit" class="button button-secondary" name="action" value="<?php echo esc_attr(Form_Entry_Retention::MANUAL_ACTION); ?>" formaction="<?php echo esc_url(admin_url('admin-post.php')); ?>" formmethod="post" onclick="return confirm('<?php echo esc_js(__('Oude formulierinzendingen permanent verwijderen?', 'tempel-settings')); ?>');"><?php esc_html_e('Nu opschonen', 'tempel-settings'); ?></button></div>
                                        </div></div>
                                    </div>
                                </div>

                                <div class="settings__form__footer">
                                    <div class="form__footer__inner">
                                        <?php submit_button(__('Instellingen opslaan', 'tempel-settings')); ?>
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

    public function is_checked($args)
    {
        $option = get_option('tmpl_settings');
        $checkbox_value = $option && is_array($option) ? ($option[$args] ?? false) : false;

        return checked('on', $checkbox_value, false);
    }

    public function get_value($key, $default = '')
    {
        $option = get_option('tmpl_settings');

        if (!$option || !is_array($option)) {
            return $default;
        }

        return $option[$key] ?? $default;
    }

    public function get_postcode_api_endpoint_value(): string
    {
        $endpoint = $this->get_value('gf_bag_address_endpoint', 'https://api.postcodeapi.nu/v3/lookup');

        if (rtrim($endpoint, '/') === 'https://api.bag.kadaster.nl/lvbag/individuelebevragingen/v2/adressenuitgebreid') {
            return 'https://api.postcodeapi.nu/v3/lookup';
        }

        if (preg_match('#^https://(api|sandbox)\.postcodeapi\.nu/v3$#', rtrim($endpoint, '/'))) {
            return rtrim($endpoint, '/') . '/lookup';
        }

        return $endpoint;
    }

    public function get_postcode_api_usage_label(): string
    {
        if (!class_exists('Tempel\GF_BAG_Address')) {
            return __('Gebruik wordt bijgehouden zodra het veld actief is.', 'tempel-settings');
        }

        $count = GF_BAG_Address::get_usage_count();
        $limit = GF_BAG_Address::get_monthly_limit();

        if ($limit <= 0) {
            return sprintf(__('Deze maand gebruikt: %d aanvragen. Limietcontrole staat uit.', 'tempel-settings'), $count);
        }

        return sprintf(__('Deze maand gebruikt: %1$d van %2$d aanvragen.', 'tempel-settings'), $count, $limit);
    }

    public function get_masked_postcode_api_key(): string
    {
        $api_key = (string) $this->get_value('gf_bag_address_api_key', '');
        $length = strlen($api_key);

        if ($length === 0) {
            return '';
        }

        if ($length <= 8) {
            return str_repeat('*', $length);
        }

        return substr($api_key, 0, 4) . str_repeat('*', max(4, $length - 8)) . substr($api_key, -4);
    }

}
