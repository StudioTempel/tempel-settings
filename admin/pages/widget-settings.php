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
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="settings__body">
                        <div class="body__inner">
                            <form action="options.php" method="post">
                                <?php settings_fields('tempel_widget_settings'); ?>

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
                                            <div class="settings__field" id="conversion_selected_forms_settings">
                                                <div class="settings__field__inner">
                                                    <div class="settings__label__wrap">
                                                        <label for="conversion_selected_forms">
                                                            <?php _e('Select forms to show in widget', 'tempel-settings'); ?>
                                                        </label>
                                                    </div>
                                                    <div class="settings__input__wrap">
                                                        <?php
                                                        $selected_forms = $this->get_settings('conversion_selected_forms');
                                                        if (!is_array($selected_forms) && $selected_forms) {
                                                            $selected_forms = explode(',', $selected_forms);
                                                        }
                                                        ?>
                                                        <select class="settings__input"
                                                                name="tmpl_widget_settings[conversion_selected_forms][]"
                                                                id="conversion_selected_forms"
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

                                        <!-- Settings Field | Update Interval -->
                                        <?php
                                            $days = [
                                                'monday' => __('Monday', 'tempel-settings'),
                                                'tuesday' => __('Tuesday', 'tempel-settings'),
                                                'wednesday' => __('Wednesday', 'tempel-settings'),
                                                'thursday' => __('Thursday', 'tempel-settings'),
                                                'friday' => __('Friday', 'tempel-settings'),
                                                'saturday' => __('Saturday', 'tempel-settings'),
                                                'sunday' => __('Sunday', 'tempel-settings'),
                                            ];
                                        ?>
                                        <div id="status_safeupdate_day_setting" class="settings__field">
                                            <div class="settings__field__inner">
                                                <div class="settings__label__wrap">
                                                    <label for="status_safeupdate_day">
                                                        <?php _e('Safeupdate day', 'tempel-settings'); ?>
                                                    </label>
                                                </div>
                                                <div class="settings__input__wrap">
                                                    <select class="settings__input"
                                                            name="tmpl_widget_settings[status_safeupdate_day]"
                                                            id="status_safeupdate_day"
                                                    >
                                                        <option value=""><?php _e('Selecteer de dag waarop de safeupdate plaatsvind', 'tempel-settings'); ?></option>
                                                        <?php foreach ($days as $key => $day): ?>
                                                            <option value="<?= $key; ?>"
                                                                <?php if ($this->get_settings('status_safeupdate_day') === $key): ?>
                                                                    selected
                                                                <?php endif; ?>
                                                            >
                                                                <?= $day; ?>
                                                            </option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                        <!-- Settings Field | Update Interval -->

                                        <!-- Settings Field | Backup Interval -->
                                        <div id="status_backup_interval_setting" class="settings__field">
                                            <div class="settings__field__inner">
                                                <div class="settings__label__wrap">
                                                    <label for="status_backup_interval">
                                                        <?php _e('Backup interval', 'tempel-settings'); ?>
                                                    </label>
                                                </div>
                                                <div class="settings__input__wrap">
                                                    <input
                                                            type="time"
                                                            class="settings__input"
                                                            name="tmpl_widget_settings[status_backup_interval]"
                                                            id="status_backup_interval"
                                                            placeholder="00:00"
                                                        <?php if ($this->get_settings('status_backup_interval')): ?>
                                                            value="<?= $this->get_settings('status_backup_interval'); ?>"
                                                        <?php endif; ?>
                                                    >
                                                </div>
                                            </div>
                                        </div>
                                        <!-- Settings Field | Backup Interval -->
                                        
                                        <!-- Settings Field | Reset Checkup -->
                                        <div id="status_reset_checkup_date_setting" class="settings__field">
                                            <div class="settings__field__inner">
                                                <div class="settings__label__wrap">
                                                    <label for="status_last_checkup_date">
                                                        <?php _e('Reset checkup'); ?>
                                                    </label>
                                                </div>
                                                <div class="settings__input__wrap">
                                                    <input
                                                            type="hidden"
                                                            name="tmpl_widget_settings[status_last_checkup_date]"
                                                            value="<?= $this->get_settings('status_last_checkup_date'); ?>"
                                                    >
                                                    <button id="reset_status_last_checkup_date" type="button"
                                                            class="button button-primary">
                                                        <?php _e('Reset checkup', 'tempel-settings'); ?>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                        <!-- Settings Field | Reset Checkup -->

                                        <!-- Settings Field | Enable Service Contract Tier -->
                                        <div id="status_show_service_contract_tier_setting" class="settings__field">
                                            <div class="settings__field__inner">
                                                <div class="settings__label__wrap">
                                                    <label for="status_show_service_contract_tier">
                                                        <?php _e('Show service contract', 'tempel-settings'); ?>
                                                    </label>
                                                </div>
                                                <div class="settings__input__wrap">
                                                    <label class="checkbox__switch" for="status_show_service_contract_tier">
                                                        <input
                                                                type="checkbox"
                                                                name="tmpl_widget_settings[status_show_service_contract_tier]"
                                                                id="status_show_service_contract_tier"
                                                            <?php echo $this->is_checked('status_show_service_contract_tier'); ?>
                                                        >
                                                        <span class="checkbox__switch__slider"></span>
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                                        <!-- Settings Field | Enable Service Contract Tier  -->

                                        <!-- Settings Field | Enable Service Upgrade Link -->
                                        <div id="status_service_contract_upgradable_setting" class="settings__field">
                                            <div class="settings__field__inner">
                                                <div class="settings__label__wrap">
                                                    <label for="status_service_contract_upgradable">
                                                        <?php _e('Service contract is upgradable', 'tempel-settings'); ?>
                                                    </label>
                                                </div>
                                                <div class="settings__input__wrap">
                                                    <label class="checkbox__switch" for="status_service_contract_upgradable">
                                                        <input
                                                                type="checkbox"
                                                                name="tmpl_widget_settings[status_service_contract_upgradable]"
                                                                id="status_service_contract_upgradable"
                                                            <?php echo $this->is_checked('status_service_contract_upgradable'); ?>
                                                        >
                                                        <span class="checkbox__switch__slider"></span>
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                                        <!-- Settings Field | Enable Service Upgrade Link  -->

                                        <!-- Settings Field | Service Contract Tier -->
                                        <div id="status_service_contract_tier_setting" class="settings__field">
                                            <div class="settings__field__inner">
                                                <div class="settings__label__wrap">
                                                    <label for="status_service_contract_tier">
                                                        <?php _e('Service contract tier', 'tempel-settings'); ?>
                                                    </label>
                                                </div>
                                                <div class="settings__input__wrap">
                                                    <input
                                                            type="text"
                                                            name="tmpl_widget_settings[status_service_contract_tier]"
                                                            id="status_service_contract_tier"
                                                            class="settings__input"
                                                        <?php if ($this->get_settings('status_service_contract_tier')): ?>
                                                            value="<?= $this->get_settings('status_service_contract_tier'); ?>"
                                                        <?php endif; ?>
                                                    >
                                                </div>
                                            </div>
                                        </div>
                                        <!-- Settings Field | Service Contact Tier -->

                                        <!-- Settings Field | Service Contract Info URL -->
                                        <div id="status_service_contract_upgrade_link_setting" class="settings__field">
                                            <div class="settings__field__inner">
                                                <div class="settings__label__wrap">
                                                    <label for="status_service_contract_upgrade_link">
                                                        <?php _e('Service contract info link', 'tempel-settings'); ?>
                                                    </label>
                                                </div>
                                                <div class="settings__input__wrap">
                                                    <input
                                                            type="url"
                                                            class="settings__input"
                                                            name="tmpl_widget_settings[status_service_contract_upgrade_link]"
                                                            id="status_service_contract_upgrade_link"
                                                            placeholder="https://studiotempel.nl/pakketten/"
                                                        <?php if ($this->get_settings('status_service_contract_upgrade_link')): ?>
                                                            value="<?= $this->get_settings('status_service_contract_upgrade_link'); ?>"
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
                                        <div id="support_faq_link_setting" class="settings__field">
                                            <div class="settings__field__inner">
                                                <div class="settings__label__wrap">
                                                    <label for="support_faq_link">
                                                        <?php _e('FAQ Link', 'tempel-settings'); ?>
                                                    </label>
                                                </div>
                                                <div class="settings__input__wrap">
                                                    <input
                                                            type="url"
                                                            class="settings__input"
                                                            name="tmpl_widget_settings[support_faq_link]"
                                                            id="support_faq_link"
                                                            placeholder="https://studiotempel.nl/faq/"
                                                        <?php if ($this->get_settings('support_faq_link')): ?>
                                                            value="<?= $this->get_settings('support_faq_link'); ?>"
                                                        <?php endif; ?>
                                                    >
                                                </div>
                                            </div>
                                        </div>
                                        <!-- Settings Field | FAQ Link -->
                                        
                                        <!-- Settings Field | Support Ticket Link -->
                                        <div class="settings__field" id="support_ticket_link_setting">
                                            <div class="settings__field__inner">
                                                <div class="settings__label__wrap">
                                                    <label for="support_ticket_link">
                                                        <?php _e('Support Ticket Link', 'tempel-settings'); ?>
                                                    </label>
                                                </div>
                                                <div class="settings__input__wrap">
                                                    <input
                                                            type="url"
                                                            class="settings__input"
                                                            name="tmpl_widget_settings[support_ticket_link]"
                                                            id="support_ticket_link"
                                                            placeholder="https://studiotempel.nl/support/"
                                                        <?php if ($this->get_settings('support_ticket_link')): ?>
                                                            value="<?= $this->get_settings('support_ticket_link'); ?>"
                                                        <?php endif; ?>
                                                    >
                                                </div>
                                            </div>
                                        </div>
                                        <!-- Settings Field | Support Ticket Link -->

                                        <!-- Settings Field | Clear FAQ Cache -->
                                        <div id="support_clear_faq_cache_setting" class="settings__field">
                                            <div class="settings__field__inner">
                                                <div class="settings__label__wrap">
                                                    <label for="support_clear_faq_cache">
                                                        <?php _e('Clear FAQ Cache', 'tempel-settings'); ?>
                                                    </label>
                                                </div>
                                                <div class="settings__input__wrap">
                                                    <button id="support_clear_faq_cache" type="button"
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
                $('#conversion_selected_forms').select2({
                    placeholder: 'Selecteer formulieren',
                    allowClear: true,
                    multiple: true
                });
                
                $('#status_safeupdate_day').select2({
                    minimumResultsForSearch: -1,
                    placeholder: 'Selecteer de dag waarop de safeupdate plaatsvind'
                });
                
                $('#total-conversion-scope').select2({
                    minimumResultsForSearch: -1
                });
                // $('input#update-interval').flatpickr({
                //     enableTime: true,
                //     dateFormat: "H:i",
                //     time_24hr: true,
                //     noCalendar: true
                // })
                // Init flatpickr for backup interval
                $('#status_backup_interval').flatpickr({
                    enableTime: true,
                    dateFormat: "H:i",
                    time_24hr: true,
                    noCalendar: true
                });



                $('button#reset_status_last_checkup_date').on('click', function () {
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
                
                // $('button#reset-last-update-date').on('click', function () {
                //     if (confirm('Weet je zeker dat je de laatste update datum wilt resetten?')) {
                //         $.ajax({
                //             url: ajaxurl,
                //             type: 'POST',
                //             data: {
                //                 action: 'reset_update',
                //             },
                //             success: function (response) {
                //                 alert('Laatste update datum is gereset');
                //                 console.log(response);
                //             },
                //             error: function (error) {
                //                 console.error(error);
                //             }
                //         });
                //     }
                // });
                
                $('button#support_clear_faq_cache').on('click', function () {
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
        $options = get_option('tmpl_widget_settings');
        
        if (!isset($options[$option])) {
            return '';
        }
        
        return $options[$option];
    }
    
    public function is_checked($args)
    {
        $option = get_option('tmpl_widget_settings');
        if ($option) {
            $checkbox_value = $option[$args] ?? false;
        } else {
            $checkbox_value = false;
        }
        
        return checked("on", $checkbox_value, false);
    }
}