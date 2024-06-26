<?php

namespace Tempel\Admin\Pages;

require_once TMPL_PLUGIN_DIR . 'admin/abstract/Page.php';

use Tempel\Admin\Abstract\Page;

class WidgetSettingsPage extends Page
{
    public function render()
    {
        $selectable_forms = $this->get_forms();
        
        ?>
        <div class="tmpl_settings__wrap">
            <div class="tmpl_settings__page" id="tmpl_widget_settings">
                <div class="tmpl_settings__inner">
                    <div class="settings__header">
                        <div class="header__inner">
                            <div class="header__title">
                                <?php _e('Widget Settings', 'tempel-settings'); ?>
                            </div>
                            <div class="header__nav">
                                <div class="nav__inner">
                                    <a href="/wp-admin/admin.php?page=tempel-settings" class="nav__item">
                                        <?php _e('General Settings', 'tempel-settings'); ?>
                                    </a>
                                    <a href="/wp-admin/admin.php?page=tempel-widget-settings" class="nav__item active">
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
                                <?php settings_fields('tempel-widget-settings'); ?>

                                <!-- Settings Category -->
                                <div class="settings__category">
                                    <div class="category__header">
                                        <div class="category__title">
                                            <?php _e('Conversion Widget', 'tempel-settings'); ?>
                                        </div>
                                        <div class="category__description">
                                            <?php _e('Settings for the conversion widget', 'tempel-settings'); ?>
                                        </div>
                                    </div>
                                    <div class="category__content">
                                        
                                        <!-- Settings Field | Selectable Forms -->
                                        <?php if ($selectable_forms): ?>
                                            <div class="settings__field" id="gf_form_select">
                                                <div class="settings__field__inner">
                                                    <div class="settings__label__wrap">
                                                        <label for="gf_form_select_field">
                                                            <?php _e('Selectable Forms', 'tempel-settings'); ?>
                                                        </label>
                                                    </div>
                                                    <div class="settings__input__wrap">
                                                        <?php
                                                        $selected_forms = $this->get_settings('gf_form_select_field');
                                                        if (!is_array($selected_forms) && $selected_forms) {
                                                            $selected_forms = explode(',', $selected_forms);
                                                        }
                                                        ?>
                                                        <select class="settings__input"
                                                                name="tempel-widget-settings-data[gf_form_select_field][]"
                                                                id="gf_form_select_field"
                                                                multiple
                                                        >
                                                            <?php foreach ($selectable_forms as $form):
                                                                $is_selected = '';
                                                                
                                                                if (is_array($selected_forms) && in_array($form['id'], $selected_forms)) {
                                                                    $is_selected = 'selected';
                                                                }
                                                                ?>
                                                                <option
                                                                        value="<?= $form['id']; ?>"
                                                                    <?= $is_selected; ?>
                                                                >
                                                                    <?= $form['title']; ?>
                                                                </option>
                                                            <?php endforeach; ?>
                                                        </select>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php endif; ?>
                                        <!-- Settings Field | Selectable Forms -->

                                        <!-- Settings Field | Total Conversions Scope -->
<!--                                        <div class="settings__field" id="total_conversions_scope">-->
<!--                                            <div class="settings__field__inner">-->
<!--                                                <div class="settings__label__wrap">-->
<!--                                                    <label for="gf_form_select_field">-->
<!--                                                        --><?php //_e('Scope Total Conversions', 'tempel'); ?>
<!--                                                    </label>-->
<!--                                                </div>-->
<!--                                                <div class="settings__input__wrap">-->
<!--                                                    <div class="settings__input">-->
<!--                                                        <select-->
<!--                                                                class="settings__input"-->
<!--                                                                name="tempel-widget-settings-data[total-conversion-scope]"-->
<!--                                                                id="total-conversion-scope"-->
<!--                                                        >-->
<!--                                                            <option selected value="last30days">--><?php //_e('Last 30 days', 'tempel-settings'); ?><!--</option>-->
<!--                                                            <option value="last7days">--><?php //_e('Last 7 days', 'tempel-settings'); ?><!--</option>-->
<!--                                                            <option value="today">--><?php //_e('Today', 'tempel-settings'); ?><!--</option>-->
<!--                                                        </select>-->
<!--                                                    </div>-->
<!--                                                </div>-->
<!--                                            </div>-->
<!--                                        </div>-->
                                        <!-- Settings Field | Total Conversions Scope -->
                                        
                                    </div>
                                </div>
                                <div class="settings__category">
                                    <div class="category__header">
                                        <div class="category__title">
                                            <?php _e('Status Widget', 'tempel-settings'); ?>
                                        </div>
                                        <div class="category__description">
                                            <?php _e('Settings for the status widget', 'tempel-settings'); ?>
                                        </div>
                                    </div>
                                    <div class="category__content">

                                        <!-- Settings Field | Backup Interval -->
                                        <div id="backup-interval" class="settings__field">
                                            <div class="settings__field__inner">
                                                <div class="settings__label__wrap">
                                                    <label for="backup-interval-field">
                                                        <?php _e('Backup interval', 'tempel-settings'); ?>
                                                    </label>
                                                </div>
                                                <div class="settings__input__wrap">
                                                    <input
                                                            type="time"
                                                            class="settings__input"
                                                            name="tempel-widget-settings-data[backup-interval-field]"
                                                            id="backup-interval-field"
                                                            placeholder="00:00"
                                                        <?php if ($this->get_settings('backup-interval-field')): ?>
                                                            value="<?= $this->get_settings('backup-interval-field'); ?>"
                                                        <?php endif; ?>
                                                    >
                                                </div>
                                            </div>
                                        </div>
                                        <!-- Settings Field | Backup Interval -->
                                        
                                        <!-- Settings Field | Reset Checkup -->
                                        <div id="reset-checkup" class="settings__field">
                                            <div class="settings__field__inner">
                                                <div class="settings__label__wrap">
                                                    <label for="reset-checkup-field">
                                                        <?php _e('Reset checkup'); ?>
                                                    </label>
                                                </div>
                                                <div class="settings__input__wrap">
                                                    <input
                                                            type="hidden"
                                                            name="tempel-widget-settings-data[last-checkup-date]"
                                                            value="<?= $this->get_settings('last-checkup-date'); ?>"
                                                    >
                                                    <button id="reset-checkup-button" type="button"
                                                            class="button button-primary">
                                                        <?php _e('Reset checkup', 'tempel-settings'); ?>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                        <!-- Settings Field | Reset Checkup -->

                                        <!-- Settings Field | Reset Update Date -->
                                        <div id="reset-last-update-date" class="settings__field">
                                            <div class="settings__field__inner">
                                                <div class="settings__label__wrap">
                                                    <label for="reset-checkup-field">
                                                        <?php _e('Reset last update date', 'tempel-settings'); ?>
                                                    </label>
                                                </div>
                                                <div class="settings__input__wrap">
                                                    <input
                                                            type="hidden"
                                                            name="tempel-widget-settings-data[last-update-date]"
                                                            value="<?= $this->get_settings('last-update-date'); ?>"
                                                    >
                                                    <button id="reset-last-update-date" type="button"
                                                            class="button button-primary">
                                                        <?php _e('Reset date', 'tempel-settings'); ?>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                        <!-- Settings Field | Reset Update Date -->

                                        <!-- Settings Field | Package Type -->
                                        <div id="package-type" class="settings__field">
                                            <div class="settings__field__inner">
                                                <div class="settings__label__wrap">
                                                    <label for="package-type-field">
                                                        <?php _e('Package Type', 'tempel-settings'); ?>
                                                    </label>
                                                </div>
                                                <div class="settings__input__wrap">
                                                    <?php
                                                    $options = array(
                                                        'basic' => 'Basic',
                                                        'pro' => 'Pro'
                                                    );
                                                    
                                                    $package_type_selected = $this->get_settings('package-type-field');
                                                    ?>
                                                    <select
                                                            class="settings__input"
                                                            name="tempel-widget-settings-data[package-type-field]"
                                                            id="package-type-field"
                                                    >
                                                        <?php foreach ($options as $key => $value): ?>
                                                            <option
                                                                    value="<?= $key; ?>"
                                                                <?php if ($package_type_selected == $key) echo 'selected'; ?>>
                                                                <?= $value; ?>
                                                            </option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                        <!-- Settings Field | Package Type -->

                                        <!-- Settings Field | Package URL -->
                                        <div id="package-url" class="settings__field">
                                            <div class="settings__field__inner">
                                                <div class="settings__label__wrap">
                                                    <label for="package-url-field">
                                                        <?php _e('Package URL', 'tempel-settings'); ?>
                                                    </label>
                                                </div>
                                                <div class="settings__input__wrap">
                                                    <input
                                                            type="url"
                                                            class="settings__input"
                                                            name="tempel-widget-settings-data[package-url-field]"
                                                            id="package-url-field"
                                                            placeholder="https://studiotempel.nl/pakketten/"
                                                        <?php if ($this->get_settings('package-type-field')): ?>
                                                            value="<?= $this->get_settings('package-url-field'); ?>"
                                                        <?php endif; ?>
                                                    >
                                                </div>
                                            </div>
                                        </div>
                                        <!-- Settings Field | Package URL -->

                                    </div>
                                </div>
                                <!-- Settings Category -->

                                <!-- Settings Category -->
                                <div class="settings__category">
                                    <div class="category__header">
                                        <div class="category__title">
                                            <?php _e('Support Widget', 'tempel-settings'); ?>
                                        </div>
                                        <div class="category__description">
                                            <?php _e('Settings for the support widget', 'tempel-settings'); ?>
                                        </div>
                                    </div>
                                    <div class="category__content">
                                        
                                        <!-- Settings Field | FAQ Link -->
                                        <div id="faq-link" class="settings__field">
                                            <div class="settings__field__inner">
                                                <div class="settings__label__wrap">
                                                    <label for="faq-link-field">
                                                        <?php _e('FAQ Link', 'tempel-settings'); ?>
                                                    </label>
                                                </div>
                                                <div class="settings__input__wrap">
                                                    <input
                                                            type="url"
                                                            class="settings__input"
                                                            name="tempel-widget-settings-data[faq-link-field]"
                                                            id="faq-link-field"
                                                            placeholder="https://studiotempel.nl/faq/"
                                                        <?php if ($this->get_settings('support-widget-faq-link')): ?>
                                                            value="<?= $this->get_settings('support-widget-faq-link'); ?>"
                                                        <?php endif; ?>
                                                    >
                                                </div>
                                            </div>
                                        </div>
                                        <!-- Settings Field | FAQ Link -->
                                        
                                        <!-- Settings Field | Support Ticket Link -->
                                        <div class="settings__field" id="support-ticket-link">
                                            <div class="settings__field__inner">
                                                <div class="settings__label__wrap">
                                                    <label for="support-ticket-link-field">
                                                        <?php _e('Support Ticket Link', 'tempel-settings'); ?>
                                                    </label>
                                                </div>
                                                <div class="settings__input__wrap">
                                                    <input
                                                            type="url"
                                                            class="settings__input"
                                                            name="tempel-widget-settings-data[support-ticket-link-field]"
                                                            id="support-ticket-link-field"
                                                            placeholder="https://studiotempel.nl/support/"
                                                        <?php if ($this->get_settings('support-widget-ticker-link')): ?>
                                                            value="<?= $this->get_settings('support-widget-ticket-link'); ?>"
                                                        <?php endif; ?>
                                                    >
                                                </div>
                                            </div>
                                        </div>
                                        <!-- Settings Field | Support Ticket Link -->

                                        <!-- Settings Field | Clear FAQ Cache -->
                                        <div id="clear-faq-cache" class="settings__field">
                                            <div class="settings__field__inner">
                                                <div class="settings__label__wrap">
                                                    <label for="reset-checkup-field">
                                                        <?php _e('Clear FAQ Cache', 'tempel-settings'); ?>
                                                    </label>
                                                </div>
                                                <div class="settings__input__wrap">
                                                    <button id="clear-faq-cache" type="button"
                                                            class="button button-primary">
                                                        <?php _e('Clear', 'tempel-settings'); ?>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                        <!-- Settings Field | Clear FAQ Cache -->
                                        
                                    </div>
                                </div>
                                <!-- Settings Category -->
                                
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
        <script>
            jQuery(document).ready(function ($) {

                // Init select2 for forms
                $('#gf_form_select_field').select2({
                    placeholder: 'Selecteer formulieren',
                    allowClear: true,
                    multiple: true
                });

                // Init select2 for package type
                $('#package-type-field').select2({
                    allowClear: true,
                    minimumResultsForSearch: -1
                });
                
                $('#total-conversion-scope').select2({
                    minimumResultsForSearch: -1
                });

                // Init flatpickr for backup interval
                $('#backup-interval-field').flatpickr({
                    enableTime: true,
                    dateFormat: "H:i",
                    time_24hr: true,
                    noCalendar: true
                });

                $('button#reset-checkup-button').on('click', function () {
                    if (confirm('Weet je zeker dat je de checkup wilt resetten?')) {
                        $.ajax({
                            url: ajaxurl,
                            type: 'POST',
                            data: {
                                action: 'reset_checkup',
                            },
                            success: function (response) {
                                alert('Checkup is gereset');
                                console.log(response);
                            },
                            error: function (error) {
                                console.error(error);
                            }
                        });
                    }
                });
                
                $('button#reset-last-update-date').on('click', function () {
                    if (confirm('Weet je zeker dat je de laatste update datum wilt resetten?')) {
                        $.ajax({
                            url: ajaxurl,
                            type: 'POST',
                            data: {
                                action: 'reset_update',
                            },
                            success: function (response) {
                                alert('Laatste update datum is gereset');
                                console.log(response);
                            },
                            error: function (error) {
                                console.error(error);
                            }
                        });
                    }
                });
                
                $('button#clear-faq-cache').on('click', function () {
                    if (confirm('Weet je zeker dat je de FAQ cache wilt legen?')) {
                        $.ajax({
                            url: ajaxurl,
                            type: 'POST',
                            data: {
                                action: 'clear_faq_cache',
                            },
                            success: function (response) {
                                alert('FAQ cache is geleegd');
                                console.log(response);
                            },
                            error: function (error) {
                                console.error(error);
                            }
                        });
                    }
                });

            });
        </script>
        <?php
    }
    
    public function enqueue_scripts(): void
    {
        if(isset($_GET['page']) && $_GET['page'] === 'tempel-widget-settings') {
            // Vendor assets
            // Select2
            wp_enqueue_style('tmpl-settings-select2', plugin_dir_url(__FILE__) . '../../dist/vendor/select2.min.css');
            wp_enqueue_script('tmpl-settings-select2', plugin_dir_url(__FILE__) . '../../dist/vendor/select2.full.min.js');
            // Flatpickr
            wp_enqueue_style('tmpl-settings-flatpickr.min.js', plugin_dir_url(__FILE__) . '../../dist/vendor/flatpickr.min.css');
            wp_enqueue_script('tmpl-settings-flatpickr.min.js', plugin_dir_url(__FILE__) . '../../dist/vendor/flatpickr.min.js');
        }
    }
    
    public function get_forms(): array
    {
        $forms = \GFAPI::get_forms();
        
        if (is_wp_error($forms)) {
            return [];
        }
        
        $selectable_forms = [];
        
        foreach ($forms as $form) {
            $form_id = $form['id'];
            $form_title = $form['title'];
            
            $selectable_forms[] = [
                'id' => $form_id,
                'title' => $form_title
            ];
        }
        
        return $selectable_forms;
    }
    
    public function get_settings($option)
    {
        $options = get_option('tempel-widget-settings-data');
        
        if (!isset($options[$option])) {
            return '';
        }
        
        return $options[$option];
    }
}