<?php

namespace Tempel\Admin\Pages;

require_once TMPL_PLUGIN_DIR . 'admin/abstract/Page.php';

use Tempel\Admin\Abstract\Page;

class GeneralSettingsPage extends Page
{
    public function render()
    {
        ?>
        <div class="tmpl_settings__wrap">
            <div class="tmpl_settings__page" id="tmpl_widget_settings">
                <div class="tmpl_settings__inner">
                    <div class="settings__header">
                        <div class="header__inner">
                            <div class="header__title">
                                <?php _e('General Settings', 'tempel-settings'); ?>
                            </div>
                            <div class="header__nav">
                                <div class="nav__inner">
                                    <a href="/wp-admin/admin.php?page=tempel-settings" class="nav__item active">
                                        <?php _e('General Settings', 'tempel-settings'); ?>
                                    </a>
                                    <a href="/wp-admin/admin.php?page=tempel-widget-settings" class="nav__item">
                                        <?php _e('Widget Settings', 'tempel-settings'); ?>
                                    </a>
<!--                                    <a href="/wp-admin/admin.php?page=tempel-login-settings" class="nav__item">-->
                                            <?php //_e('Login Settings', 'tempel-settings'); ?>
<!--                                    </a>-->
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="settings__body">
                        <div class="body__inner">
                            <form action="options.php" method="post">
                                <?php settings_fields('tempel_settings'); ?>

                                <div class="settings__category">
                                    <div class="category__header">
                                        <div class="category__title">
                                            <?php _e('General', 'tempel-settings'); ?>
                                        </div>
                                        <div class="category__description">
                                            <?php _e('A collection of general settings to disable unused features of Wordpress.', 'tempel-settings'); ?>
                                        </div>
                                    </div>
                                    <div class="category__content">

                                        <!-- Settings Field | Enable Branding -->
                                        <div id="enable_branding_setting" class="settings__field">
                                            <div class="settings__field__inner">
                                                <div class="settings__label__wrap">
                                                    <label for="enable_branding">
                                                        <?php _e('Enable Branding', 'tempel-settings'); ?>
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

                                        <!-- Settings Field | Disable Comments -->
                                        <div id="disable_comments_setting" class="settings__field">
                                            <div class="settings__field__inner">
                                                <div class="settings__label__wrap">
                                                    <label for="disable_comments">
                                                        <?php _e('Disable Comments', 'tempel-settings'); ?>
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
                                                        <?php _e('Disable the built in PT', 'tempel-settings'); ?>
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
                                                        <?php _e('Hide Dashboard Widgets', 'tempel-settings'); ?>
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

                                    </div>
                                </div>

                                <div class="settings__category">
                                    <div class="category__header">
                                        <div class="category__title">
                                            <?php _e('Extra', 'tempel-settings'); ?>
                                        </div>
                                    </div>
                                    <div class="category__content">

                                        <!-- Settings Field | Enable Widgets -->
                                        <div id="enable_widgets_setting" class="settings__field">
                                            <div class="settings__field__inner">
                                                <div class="settings__label__wrap">
                                                    <label for="enable_widgets">
                                                        <?php _e('Enable Widgets', 'tempel-settings'); ?>
                                                    </label>
                                                </div>
                                                <div class="settings__input__wrap">
                                                    <label for="enable_widgets" class="checkbox__switch">
                                                        <input
                                                                type="checkbox"
                                                                name="tmpl_settings[enable_widgets]"
                                                                id="enable_widgets"
                                                            <?php echo $this->is_checked('enable_widgets'); ?>
                                                        >
                                                        <span class="checkbox__switch__slider"></span>
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                                        <!-- Settings Field | Enable Widgets -->

                                        <!-- Settings Field | Enable SVG Support & Sanitization -->
                                        <div id="svg_support_setting" class="settings__field">
                                            <div class="settings__field__inner">
                                                <div class="settings__label__wrap">
                                                    <label for="svg_support">
                                                        <?php _e('Enable SVG Support & Sanitization', 'tempel-settings'); ?>
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
                                
                                <!-- Settings Form Footer -->
                                <div class="settings__form__footer">
                                    <div class="form__footer__inner">
                                        <?php submit_button(__('Save Settings', 'tempel-settings')); ?>
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
}
