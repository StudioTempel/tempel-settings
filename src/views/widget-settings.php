<?php

namespace Tempel;

require_once TEMPEL_SETTINGS_DIR . 'src/abstract/page.php';
require_once TEMPEL_SETTINGS_DIR . 'src/views/partials/settings-navigation.php';

class Widget_Settings extends Page
{
    public function render()
    {
        $selectable_forms = null;
        if (class_exists('GFAPI')) {
            $selectable_forms = $this->get_forms();
        }
        $selectable_post_types = $this->get_countable_post_types();
        
        ?>
        <div class="tmpl_settings__wrap">
            <div class="tmpl_settings__page" id="tmpl_widget_settings">
                <div class="tmpl_settings__inner">
                    <?php settings_navigation(); ?>
                    <div class="settings__body">
                        <div class="body__inner">
                            <form action="options.php" method="post">
                                <?php settings_fields('tempel_widget_settings'); ?>

                                <!-- Settings Category -->
                                <div class="settings__category">
                                    <div class="category__header">
                                        <div class="category__label__wrap">
                                            <div class="category__title">
                                                <?php _e('Analytics Widget', 'tempel-settings'); ?>
                                            </div>
                                            <div class="category__description">
                                                <?php _e('Settings for the Google Site Kit visitors widget', 'tempel-settings'); ?>
                                            </div>
                                        </div>
                                        <div class="category__input__wrap">
                                            <label class="checkbox__switch" for="analytics_widget_enabled">
                                                <input
                                                        type="checkbox"
                                                        name="tmpl_widget_settings[analytics_widget_enabled]"
                                                        id="analytics_widget_enabled"
                                                    <?php echo $this->is_checked('analytics_widget_enabled'); ?>
                                                >
                                                <span class="checkbox__switch__slider"></span>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                                <!-- Settings Category -->

                                <!-- Settings Category -->
                                <div class="settings__category">
                                    <div class="category__header">
                                        <div class="category__label__wrap">
                                            <div class="category__title">
                                                <?php _e('Conversion Widget', 'tempel-settings'); ?>
                                            </div>
                                            <div class="category__description">
                                                <?php _e('Settings for the conversion widget', 'tempel-settings'); ?>
                                            </div>
                                        </div>
                                        <div class="category__input__wrap">
                                            <label class="checkbox__switch" for="conversion_widget_enabled">
                                                <input
                                                        type="checkbox"
                                                        name="tmpl_widget_settings[conversion_widget_enabled]"
                                                        id="conversion_widget_enabled"
                                                    <?php echo $this->is_checked('conversion_widget_enabled'); ?>
                                                >
                                                <span class="checkbox__switch__slider"></span>
                                            </label>
                                        </div>
                                    </div>
                                    <?php
                                    $class = '';
                                    if($this->is_checked('conversion_widget_enabled')) {
                                        $class = 'content__open';
                                    }
                                    ?>
                                    <div class="category__content content__collapsable <?= esc_attr($class); ?>">

                                        <!-- Settings Field | Selectable Forms -->

                                        <div class="settings__field" id="conversion_selected_forms_settings">
                                            <div class="settings__field__inner">
                                                <div class="settings__label__wrap">
                                                    <label for="conversion_selected_forms">
                                                        <?php _e('Select forms to show in widget', 'tempel-settings'); ?>
                                                    </label>
                                                </div>
                                                <div class="settings__input__wrap">
                                                    <?php if ($selectable_forms): ?>
                                                        <?php
                                                        $selected_forms = $this->get_settings('conversion_selected_forms');
                                                        if (!is_array($selected_forms) && $selected_forms) {
                                                            $selected_forms = explode(',', $selected_forms);
                                                        }
                                                        $selected_forms = is_array($selected_forms) ? array_map('absint', $selected_forms) : array();
                                                        ?>
                                                        <select class="settings__input"
                                                                name="tmpl_widget_settings[conversion_selected_forms][]"
                                                                id="conversion_selected_forms"
                                                                multiple
                                                        >
                                                            <?php foreach ($selectable_forms as $form):
                                                                $is_selected = '';

                                                                if (in_array((int) $form['id'], $selected_forms, true)) {
                                                                    $is_selected = 'selected';
                                                                }
                                                                ?>
                                                                <option
                                                                        value="<?= esc_attr($form['id']); ?>"
                                                                    <?= esc_attr($is_selected); ?>
                                                                >
                                                                    <?= esc_html($form['title']); ?>
                                                                </option>
                                                            <?php endforeach; ?>
                                                        </select>
                                                    <?php else: ?>
                                                        <span class="tmpl_widget__error">
                                                                <?php _e('Something went wrong. Is Gravity Forms active and does it have at least one active form', 'tempel-settings'); ?>
                                                            </span>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Settings Field | Selectable Forms -->

                                        <?php if (function_exists('wc_get_orders')): ?>
                                            <div class="settings__field" id="conversion_include_woocommerce_orders_setting">
                                                <div class="settings__field__inner">
                                                    <div class="settings__label__wrap">
                                                        <label for="conversion_include_woocommerce_orders">
                                                            <?php _e('Count WooCommerce orders as conversions', 'tempel-settings'); ?>
                                                        </label>
                                                    </div>
                                                    <div class="settings__input__wrap">
                                                        <label class="checkbox__switch" for="conversion_include_woocommerce_orders">
                                                            <input
                                                                    type="checkbox"
                                                                    name="tmpl_widget_settings[conversion_include_woocommerce_orders]"
                                                                    id="conversion_include_woocommerce_orders"
                                                                <?php echo $this->is_checked('conversion_include_woocommerce_orders'); ?>
                                                            >
                                                            <span class="checkbox__switch__slider"></span>
                                                        </label>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php endif; ?>

                                        <div class="settings__field" id="conversion_include_post_type_setting">
                                            <div class="settings__field__inner">
                                                <div class="settings__label__wrap">
                                                    <label for="conversion_include_post_type">
                                                        <?php _e('Count a post type as conversions', 'tempel-settings'); ?>
                                                    </label>
                                                </div>
                                                <div class="settings__input__wrap">
                                                    <label class="checkbox__switch" for="conversion_include_post_type">
                                                        <input
                                                                type="checkbox"
                                                                name="tmpl_widget_settings[conversion_include_post_type]"
                                                                id="conversion_include_post_type"
                                                            <?php echo $this->is_checked('conversion_include_post_type'); ?>
                                                        >
                                                        <span class="checkbox__switch__slider"></span>
                                                    </label>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="settings__field" id="post_type_count_post_type_setting">
                                            <div class="settings__field__inner">
                                                <div class="settings__label__wrap">
                                                    <label for="post_type_count_post_type">
                                                        <?php _e('Post type', 'tempel-settings'); ?>
                                                    </label>
                                                </div>
                                                <div class="settings__input__wrap">
                                                    <select class="settings__input"
                                                            name="tmpl_widget_settings[post_type_count_post_type]"
                                                            id="post_type_count_post_type"
                                                    >
                                                        <option value=""><?php _e('Select a post type', 'tempel-settings'); ?></option>
                                                        <?php foreach ($selectable_post_types as $post_type): ?>
                                                            <option value="<?= esc_attr($post_type['name']); ?>"
                                                                <?php if ($this->get_settings('post_type_count_post_type') === $post_type['name']): ?>
                                                                    selected
                                                                <?php endif; ?>
                                                            >
                                                                <?= esc_html($post_type['label']); ?>
                                                            </option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="settings__field" id="post_type_count_statuses_setting">
                                            <div class="settings__field__inner">
                                                <div class="settings__label__wrap">
                                                    <label for="post_type_count_statuses">
                                                        <?php _e('Statuses to count', 'tempel-settings'); ?>
                                                    </label>
                                                </div>
                                                <div class="settings__input__wrap">
                                                    <input
                                                            type="text"
                                                            class="settings__input"
                                                            name="tmpl_widget_settings[post_type_count_statuses]"
                                                            id="post_type_count_statuses"
                                                            placeholder="<?php esc_attr_e('Leave empty to count all statuses', 'tempel-settings'); ?>"
                                                        <?php if ($this->get_settings('post_type_count_statuses')): ?>
                                                            value="<?= esc_attr($this->get_settings('post_type_count_statuses')); ?>"
                                                        <?php endif; ?>
                                                    >
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="settings__category">
                                    <div class="category__header">
                                        <div class="category__label__wrap">
                                            <div class="category__title">
                                                <?php _e('Status Widget', 'tempel-settings'); ?>
                                            </div>
                                            <div class="category__description">
                                                <?php _e('Settings for the status widget', 'tempel-settings'); ?>
                                            </div>
                                        </div>
                                        <div class="category__input__wrap">
                                            <label class="checkbox__switch" for="status_widget_enabled">
                                                <input
                                                        type="checkbox"
                                                        name="tmpl_widget_settings[status_widget_enabled]"
                                                        id="status_widget_enabled"
                                                    <?php echo $this->is_checked('status_widget_enabled'); ?>
                                                >
                                                <span class="checkbox__switch__slider"></span>
                                            </label>
                                        </div>
                                    </div>
                                    <?php
                                    $class = '';
                                    if($this->is_checked('status_widget_enabled')) {
                                        $class = 'content__open';
                                    }
                                    ?>
                                    <div class="category__content content__collapsable <?= esc_attr($class); ?>">

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
                                                        <option value=""><?php _e('What day do the automatic updates take place', 'tempel-settings'); ?></option>
                                                        <?php foreach ($days as $key => $day): ?>
                                                            <option value="<?= esc_attr($key); ?>"
                                                                <?php if ($this->get_settings('status_safeupdate_day') === $key): ?>
                                                                    selected
                                                                <?php endif; ?>
                                                            >
                                                                <?= esc_html($day); ?>
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
                                                            value="<?= esc_attr($this->get_settings('status_backup_interval')); ?>"
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
                                                        <?php esc_html_e('Reset checkup', 'tempel-settings'); ?>
                                                    </label>
                                                </div>
                                                <div class="settings__input__wrap">
                                                    <input
                                                            type="hidden"
                                                            name="tmpl_widget_settings[status_last_checkup_date]"
                                                            value="<?= esc_attr($this->get_settings('status_last_checkup_date')); ?>"
                                                    >
                                                    <button id="reset_status_last_checkup_date" type="button"
                                                            class="button button-primary">
                                                        <?php _e('Reset checkup', 'tempel-settings'); ?>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                        <!-- Settings Field | Reset Checkup -->

                                    </div>
                                </div>
                                <!-- Settings Category -->

                                <!-- Settings Category -->
                                <div class="settings__category">
                                    <div class="category__header">
                                        <div class="category__label__wrap">
                                            <div class="category__title">
                                                <?php _e('Support Widget', 'tempel-settings'); ?>
                                            </div>
                                            <div class="category__description">
                                                <?php _e('Settings for the support widget', 'tempel-settings'); ?>
                                            </div>
                                        </div>
                                        <div class="category__input__wrap">
                                            <label class="checkbox__switch" for="support_widget_enabled">
                                                <input
                                                        type="checkbox"
                                                        name="tmpl_widget_settings[support_widget_enabled]"
                                                        id="support_widget_enabled"
                                                    <?php echo $this->is_checked('support_widget_enabled'); ?>
                                                >
                                                <span class="checkbox__switch__slider"></span>
                                            </label>
                                        </div>
                                    </div>
                                    <?php
                                        $class = '';
                                        if($this->is_checked('support_widget_enabled')) {
                                            $class = 'content__open';
                                        }
                                    ?>
                                    <div class="category__content content__collapsable <?= esc_attr($class); ?>">

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
                                                            value="<?= esc_url($this->get_settings('support_ticket_link')); ?>"
                                                        <?php endif; ?>
                                                    >
                                                </div>
                                            </div>
                                        </div>
                                        <!-- Settings Field | Support Ticket Link -->

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
        <?php
    }
    
    public function enqueue_scripts(): void
    {
        if (isset($_GET['page']) && $_GET['page'] === 'tempel-widget-settings') {
            // Vendor assets
            // Select2
            wp_enqueue_style('tmpl-settings-select2', plugin_dir_url(__FILE__) . '../../dist/vendor/select2.min.css');
            wp_enqueue_script('tmpl-settings-select2', plugin_dir_url(__FILE__) . '../../dist/vendor/select2.full.min.js');
            wp_add_inline_style(
                'tmpl-settings-select2',
                '.tmpl_settings__wrap .settings__input__wrap .select2-container{width:100%!important;min-width:360px;box-sizing:border-box}.tmpl_settings__wrap .settings__input__wrap .select2-container--default .select2-selection--single,.tmpl_settings__wrap .settings__input__wrap .select2-container--default .select2-selection--multiple{color:#fff!important;background-color:#2b2b2b!important;border-color:#444!important;min-height:48px!important;padding:0 48px 0 18px!important;border-radius:16px!important;display:flex!important;align-items:center!important}.tmpl_settings__wrap .settings__input__wrap .select2-container--default .select2-selection--single .select2-selection__rendered,.tmpl_settings__wrap .settings__input__wrap .select2-container--default .select2-selection--multiple .select2-selection__rendered,.tmpl_settings__wrap .settings__input__wrap .select2-container--default .select2-selection__placeholder{color:#fff!important;line-height:1.2!important;padding:0!important}.tmpl_settings__wrap .settings__input__wrap .select2-container--default .select2-selection--single .select2-selection__arrow{height:48px!important;right:14px!important;top:0!important;width:28px!important}.tmpl_settings__wrap .settings__input__wrap .select2-container--default .select2-selection--single .select2-selection__arrow b{border:0!important;height:18px!important;left:50%!important;margin:-9px 0 0 -9px!important;top:50%!important;width:18px!important;background-image:url("data:image/svg+xml,%3Csvg width=\'18\' height=\'18\' viewBox=\'0 0 18 18\' xmlns=\'http://www.w3.org/2000/svg\'%3E%3Cpath d=\'M4.5 6.75L9 11.25L13.5 6.75\' fill=\'none\' stroke=\'%23ffffff\' stroke-width=\'2.25\' stroke-linecap=\'round\' stroke-linejoin=\'round\'/%3E%3C/svg%3E")!important;background-position:center!important;background-repeat:no-repeat!important}body.wp-admin .select2-container--default .select2-dropdown{color:#fff;background-color:#2b2b2b;border-color:#444}body.wp-admin .select2-container--default .select2-results__option--selected,body.wp-admin .select2-container--default .select2-results__option--highlighted.select2-results__option--selectable{color:#000;background-color:#f3f438}'
            );
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

    public function get_countable_post_types(): array
    {
        $post_types = get_post_types(array('show_ui' => true), 'objects');
        $countable_post_types = array();

        foreach ($post_types as $post_type) {
            if ($post_type->name === 'attachment') {
                continue;
            }

            $countable_post_types[] = array(
                'name' => $post_type->name,
                'label' => $post_type->labels->name,
            );
        }

        if (function_exists('wc_get_orders')) {
            $has_orders = false;

            foreach ($countable_post_types as $post_type) {
                if ($post_type['name'] === 'shop_order') {
                    $has_orders = true;
                    break;
                }
            }

            if (!$has_orders) {
                $countable_post_types[] = array(
                    'name' => 'shop_order',
                    'label' => __('Orders', 'tempel-settings'),
                );
            }
        }

        usort($countable_post_types, function ($a, $b) {
            return strcasecmp($a['label'], $b['label']);
        });

        return $countable_post_types;
    }
    
    public function get_settings($option)
    {
        $options = get_option('tmpl_widget_settings');

        if (!is_array($options)) {
            return '';
        }
        
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
