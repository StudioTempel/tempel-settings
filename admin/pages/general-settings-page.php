<?php

namespace Tempel\Admin\Pages;

require_once plugin_dir_path(__FILE__) . '../class-page.php';

use Tempel\Admin\Page;

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
                                <?php settings_fields('tempel-settings'); ?>

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
                                        <div id="enable_branding" class="settings__field">
                                            <div class="settings__field__inner">
                                                <div class="settings__label__wrap">
                                                    <label for="reset-checkup-field">
                                                        <?php _e('Enable Branding', 'tempel-settings'); ?>
                                                    </label>
                                                </div>
                                                <div class="settings__input__wrap">
                                                    <label class="checkbox__switch" for="tmpl_branding">
                                                        <input
                                                                type="checkbox"
                                                                name="tempel-settings-data[tmpl_branding]"
                                                                id="tmpl_branding"
                                                            <?php echo $this->is_checked('tmpl_branding'); ?>
                                                        >
                                                        <span class="checkbox__switch__slider"></span>
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                                        <!-- Settings Field | Enable Branding -->

                                        <!-- Settings Field | Disable Comments -->
                                        <div id="disable_comments" class="settings__field">
                                            <div class="settings__field__inner">
                                                <div class="settings__label__wrap">
                                                    <label for="reset-checkup-field">
                                                        <?php _e('Disable Comments', 'tempel-settings'); ?>
                                                    </label>
                                                </div>
                                                <div class="settings__input__wrap">
                                                    <label class="checkbox__switch" for="tmpl_disable_comments">
                                                        <input
                                                                type="checkbox"
                                                                name="tempel-settings-data[tmpl_disable_comments]"
                                                                id="tmpl_disable_comments"
                                                            <?php echo $this->is_checked('tmpl_disable_comments'); ?>
                                                        >
                                                        <span class="checkbox__switch__slider"></span>
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                                        <!-- Settings Field | Disable Comments -->

                                        <!-- Settings Field | Disable Default PT -->
                                        <div id="disable_default_pt" class="settings__field">
                                            <div class="settings__field__inner">
                                                <div class="settings__label__wrap">
                                                    <label for="reset-checkup-field">
                                                        <?php _e('Disable the built in PT', 'tempel-settings'); ?>
                                                    </label>
                                                </div>
                                                <div class="settings__input__wrap">
                                                    <label for="tmpl_disable_default_posts" class="checkbox__switch">
                                                        <input
                                                                type="checkbox"
                                                                name="tempel-settings-data[tmpl_disable_default_posts]"
                                                                id="tmpl_disable_default_posts"
                                                            <?php echo $this->is_checked('tmpl_disable_default_posts'); ?>
                                                        >
                                                        <span class="checkbox__switch__slider"></span>
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                                        <!-- Settings Field | Disable Default PT -->

                                        <!-- Settings Field | Hide Dashboard Widgets -->
                                        <div id="hide_dash_widgets" class="settings__field">
                                            <div class="settings__field__inner">
                                                <div class="settings__label__wrap">
                                                    <label for="reset-checkup-field">
                                                        <?php _e('Hide Dashboard Widgets', 'tempel-settings'); ?>
                                                    </label>
                                                </div>
                                                <div class="settings__input__wrap">
                                                    <label for="tmpl_hide_dashboard_widgets" class="checkbox__switch">
                                                        <input
                                                                type="checkbox"
                                                                name="tempel-settings-data[tmpl_hide_dashboard_widgets]"
                                                                id="tmpl_hide_dashboard_widgets"
                                                            <?php echo $this->is_checked('tmpl_hide_dashboard_widgets'); ?>
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
                                        <div id="enable_widgets" class="settings__field">
                                            <div class="settings__field__inner">
                                                <div class="settings__label__wrap">
                                                    <label for="reset-checkup-field">
                                                        <?php _e('Enable Widgets', 'tempel-settings'); ?>
                                                    </label>
                                                </div>
                                                <div class="settings__input__wrap">
                                                    <label for="tmpl_enable_widget" class="checkbox__switch">
                                                        <input
                                                                type="checkbox"
                                                                name="tempel-settings-data[tmpl_enable_widget]"
                                                                id="tmpl_enable_widget"
                                                            <?php echo $this->is_checked('tmpl_enable_widget'); ?>
                                                        >
                                                        <span class="checkbox__switch__slider"></span>
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                                        <!-- Settings Field | Enable Widgets -->

                                        <!-- Settings Field | Enable SVG Support & Sanitization -->
                                        <div id="enable_svg_support" class="settings__field">
                                            <div class="settings__field__inner">
                                                <div class="settings__label__wrap">
                                                    <label for="reset-checkup-field">
                                                        <?php _e('Enable SVG Support & Sanitization', 'tempel-settings'); ?>
                                                    </label>
                                                </div>
                                                <div class="settings__input__wrap">
                                                    <label for="tmpl_enable_svg" class="checkbox__switch">
                                                        <input
                                                                type="checkbox"
                                                                name="tempel-settings-data[tmpl_enable_svg]"
                                                                id="tmpl_enable_svg"
                                                            <?php echo $this->is_checked('tmpl_enable_svg'); ?>
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
        $option = get_option('tempel-settings-data');
        if ($option) {
            $checkbox_value = $option[$args] ?? false;
        } else {
            $checkbox_value = false;
        }
        
        return checked("on", $checkbox_value, false);
    }
}
