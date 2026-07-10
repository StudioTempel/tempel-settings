<?php

namespace Tempel;

require_once TEMPEL_SETTINGS_DIR . 'src/abstract/page.php';
require_once TEMPEL_SETTINGS_DIR . 'src/views/partials/settings-navigation.php';


class General_Settings extends Page
{
    public function render()
    {
        ?>
        <div class="tmpl_settings__wrap">
            <div class="tmpl_settings__page" id="tmpl_widget_settings">
                <div class="tmpl_settings__inner">
                    <?php settings_navigation(); ?>
                    <div class="settings__body">
                        <div class="body__inner">
                            <form action="options.php" method="post">
                                <?php settings_fields('tempel_settings'); ?>

                                <div class="settings__category">
                                    <div class="category__header">
                                        <div class="category__label__wrap">
                                            <div class="category__title">
                                                <?php _e('Opschonen', 'tempel-settings'); ?>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="category__content">

                                        <!-- Settings Field | Disable Comments -->
                                        <div id="disable_comments_setting" class="settings__field">
                                            <div class="settings__field__inner">
                                                <div class="settings__label__wrap">
                                                    <label for="disable_comments">
                                                        <?php _e('Reacties uitschakelen', 'tempel-settings'); ?>
                                                    </label>
                                                </div>
                                                <div class="settings__input__wrap">
                                                    <label class="checkbox__switch" for="disable_comments">
                                                        <input
                                                                type="checkbox"
                                                                name="tmpl_settings[disable_comments]"
                                                                id="disable_comments"
                                                            <?php echo $this->is_checked('disable_comments'); ?>
                                                        >
                                                        <span class="checkbox__switch__slider"></span>
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                                        <!-- Settings Field | Disable Comments -->

                                        <!-- Settings Field | Disable Default PT -->
                                        <div id="disable_default_pt_setting" class="settings__field">
                                            <div class="settings__field__inner">
                                                <div class="settings__label__wrap">
                                                    <label for="disable_default_pt">
                                                        <?php _e('Standaard berichttype uitschakelen', 'tempel-settings'); ?>
                                                    </label>
                                                </div>
                                                <div class="settings__input__wrap">
                                                    <label for="disable_default_pt" class="checkbox__switch">
                                                        <input
                                                                type="checkbox"
                                                                name="tmpl_settings[disable_default_pt]"
                                                                id="disable_default_pt"
                                                            <?php echo $this->is_checked('disable_default_pt'); ?>
                                                        >
                                                        <span class="checkbox__switch__slider"></span>
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                                        <!-- Settings Field | Disable Default PT -->

                                        <!-- Settings Field | Hide Dashboard Widgets -->
                                        <div id="hide_dashboard_widgets_setting" class="settings__field">
                                            <div class="settings__field__inner">
                                                <div class="settings__label__wrap">
                                                    <label for="hide_dashboard_widgets">
                                                        <?php _e('Dashboard-widgets verbergen', 'tempel-settings'); ?>
                                                    </label>
                                                </div>
                                                <div class="settings__input__wrap">
                                                    <label for="hide_dashboard_widgets" class="checkbox__switch">
                                                        <input
                                                                type="checkbox"
                                                                name="tmpl_settings[hide_dashboard_widgets]"
                                                                id="hide_dashboard_widgets"
                                                            <?php echo $this->is_checked('hide_dashboard_widgets'); ?>
                                                        >
                                                        <span class="checkbox__switch__slider"></span>
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                                        <!-- Settings Field | Hide Dashboard Widgets -->

                                        <!-- Settings Field | Skip Bundled Themes -->
                                        <div id="skip_bundled_themes_setting" class="settings__field">
                                            <div class="settings__field__inner">
                                                <div class="settings__label__wrap">
                                                    <label for="skip_bundled_themes">
                                                        <?php _e('Twenty-thema\'s niet automatisch downloaden', 'tempel-settings'); ?>
                                                    </label>
                                                </div>
                                                <div class="settings__input__wrap">
                                                    <label for="skip_bundled_themes" class="checkbox__switch">
                                                        <input
                                                                type="checkbox"
                                                                name="tmpl_settings[skip_bundled_themes]"
                                                                id="skip_bundled_themes"
                                                            <?php echo $this->is_checked('skip_bundled_themes'); ?>
                                                        >
                                                        <span class="checkbox__switch__slider"></span>
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                                        <!-- Settings Field | Skip Bundled Themes -->

                                    </div>
                                </div>

                                <div class="settings__category">
                                    <div class="category__header">
                                        <div class="category__label__wrap">
                                            <div class="category__title">
                                                <?php _e('Uiterlijk', 'tempel-settings'); ?>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="category__content">

                                        <!-- Settings Field | Enable Branding -->
                                        <div id="enable_branding_setting" class="settings__field">
                                            <div class="settings__field__inner">
                                                <div class="settings__label__wrap">
                                                    <label for="enable_branding">
                                                        <?php _e('Branding inschakelen', 'tempel-settings'); ?>
                                                    </label>
                                                </div>
                                                <div class="settings__input__wrap">
                                                    <label class="checkbox__switch" for="enable_branding">
                                                        <input
                                                                type="checkbox"
                                                                name="tmpl_settings[enable_branding]"
                                                                id="enable_branding"
                                                            <?php echo $this->is_checked('enable_branding'); ?>
                                                        >
                                                        <span class="checkbox__switch__slider"></span>
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                                        <!-- Settings Field | Enable Branding -->

                                    </div>
                                </div>

                                <div class="settings__category">
                                    <div class="category__header">
                                        <div class="category__label__wrap">
                                            <div class="category__title">
                                                <?php _e('Media', 'tempel-settings'); ?>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="category__content">

                                        <!-- Settings Field | Enable SVG Support & Sanitization -->
                                        <div id="svg_support_setting" class="settings__field">
                                            <div class="settings__field__inner">
                                                <div class="settings__label__wrap">
                                                    <label for="svg_support">
                                                        <?php _e('SVG-ondersteuning en sanitization inschakelen', 'tempel-settings'); ?>
                                                    </label>
                                                </div>
                                                <div class="settings__input__wrap">
                                                    <label for="svg_support" class="checkbox__switch">
                                                        <input
                                                                type="checkbox"
                                                                name="tmpl_settings[svg_support]"
                                                                id="svg_support"
                                                            <?php echo $this->is_checked('svg_support'); ?>
                                                        >
                                                        <span class="checkbox__switch__slider"></span>
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                                        <!-- Settings Field | Enable SVG Support & Sanitization -->
                                    </div>
                                </div>

                                <div class="settings__category">
                                    <div class="category__header">
                                        <div class="category__label__wrap">
                                            <div class="category__title">
                                                <?php _e('Helpers', 'tempel-settings'); ?>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="category__content">

                                        <!-- Settings Field | Enable Taxonomy Order -->
                                        <div id="taxonomy_order_setting" class="settings__field">
                                            <div class="settings__field__inner">
                                                <div class="settings__label__wrap">
                                                    <label for="taxonomy_order">
                                                        <?php _e('Taxonomie-volgorde inschakelen', 'tempel-settings'); ?>
                                                    </label>
                                                </div>
                                                <div class="settings__input__wrap">
                                                    <label for="taxonomy_order" class="checkbox__switch">
                                                        <input
                                                                type="checkbox"
                                                                name="tmpl_settings[taxonomy_order]"
                                                                id="taxonomy_order"
                                                            <?php echo $this->is_checked('taxonomy_order'); ?>
                                                        >
                                                        <span class="checkbox__switch__slider"></span>
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                                        <!-- Settings Field | Enable Taxonomy Order -->

                                    </div>
                                </div>

                                <!-- Settings Form Footer -->
                                <div class="settings__form__footer">
                                    <div class="form__footer__inner">
                                        <?php submit_button(__('Instellingen opslaan', 'tempel-settings')); ?>
                                    </div>
                                </div>
                                <!-- Settings Form Footer -->
                            </form>
                        </div>
                    </div>
                    <div class="settings__footer">

                    </div>
                </div>
            </div>
        </div>
        <?php
    }
    
    public function is_checked($args)
    {
        $option = get_option('tmpl_settings');
        if ($option) {
            $checkbox_value = $option[$args] ?? false;
        } else {
            $checkbox_value = false;
        }
        
        return checked("on", $checkbox_value, false);
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
}
