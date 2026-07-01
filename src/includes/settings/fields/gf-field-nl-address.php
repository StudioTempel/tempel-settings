<?php

if (!class_exists('GFForms') || !class_exists('GF_Field')) {
    return;
}

class GF_Field_NL_Address extends GF_Field
{
    public $type = 'nl_address_lookup';

    public $manualInput = false;

    public $loadingMessage = '';

    public $notFoundMessage = '';

    public $requiredAddressMessage = '';

    public $incompleteAddressMessage = '';

    public $hideFieldLabel = false;

    public $hideSubLabels = false;

    public $postcodePlaceholder = 'Postcode';

    public $houseNumberPlaceholder = 'Huisnr';

    public $additionPlaceholder = 'Toev.';

    public $streetPlaceholder = 'Straat';

    public $cityPlaceholder = 'Plaats';

    public function get_form_editor_field_title()
    {
        return esc_attr__('Adres NL (Postcode API)', 'tempel-settings');
    }

    public function get_form_editor_button()
    {
        return array(
            'group' => 'advanced_fields',
            'text' => $this->get_form_editor_field_title(),
        );
    }

    public function get_form_editor_field_icon()
    {
        return 'gform-icon--place';
    }

    public function get_form_editor_field_settings()
    {
        return array(
            'label_setting',
            'admin_label_setting',
            'description_setting',
            'rules_setting',
            'error_message_setting',
            'tempel_manual_input_setting',
            'tempel_address_display_setting',
            'tempel_address_placeholders_setting',
            'tempel_address_messages_setting',
            'css_class_setting',
            'conditional_logic_field_setting',
        );
    }

    public function is_conditional_logic_supported()
    {
        return true;
    }

    public function get_field_input($form, $value = '', $entry = null)
    {
        $form_id = (int) rgar($form, 'id');
        $id = (int) $this->id;
        $is_entry_detail = $this->is_entry_detail();
        $is_form_editor = $this->is_form_editor();

        if ($is_form_editor) {
            $this->enqueue_address_styles();
            return $this->get_form_editor_preview();
        }

        $field_id = $is_entry_detail || $form_id === 0 ? "input_{$id}" : "input_{$form_id}_{$id}";
        $tabindex = $this->get_tabindex();
        $disabled = $is_entry_detail ? "disabled='disabled'" : '';
        $required_inputs = $this->get_required_inputs_ids();

        $values = is_array($value) ? $value : array();
        $postcode = esc_attr(rgget($this->id . '.1', $values));
        $huisnummer = esc_attr(rgget($this->id . '.2', $values));
        $toevoeging = esc_attr(rgget($this->id . '.3', $values));
        $straat = esc_attr(rgget($this->id . '.4', $values));
        $plaats = esc_attr(rgget($this->id . '.5', $values));
        $manual_input = $this->is_manual_input_enabled();
        $messages = $this->get_address_messages();

        wp_enqueue_script(
            'tmpl-gf-bag-address-frontend',
            TEMPEL_SETTINGS_ASSET_URL . 'js/gf-bag-address.js',
            array('jquery'),
            TEMPEL_SETTINGS_VERSION,
            true
        );

        $this->enqueue_address_styles();

        $script_handle = 'tmpl-gf-bag-address-field-' . $form_id . '-' . $id;
        wp_register_script($script_handle, '', array('tmpl-gf-bag-address-frontend'), TEMPEL_SETTINGS_VERSION, true);
        wp_enqueue_script($script_handle);
        wp_add_inline_script(
            $script_handle,
            'window.GFBagAddressFieldData = window.GFBagAddressFieldData || {}; window.GFBagAddressFieldData["' . esc_js($field_id) . '"] = ' . wp_json_encode(\Tempel\GF_BAG_Address::get_ajax_config($id, $manual_input, $messages)) . ';',
            'before'
        );

        $message_id = $field_id . '_message';
        $sub_label_class = $this->hide_sub_labels() ? ' class="gfbag-visually-hidden"' : '';

        $inputs = array(
            array('index' => 1, 'label' => __('Postcode', 'tempel-settings'), 'value' => $postcode, 'class' => 'gfbag-postcode', 'placeholder' => $this->postcodePlaceholder),
            array('index' => 2, 'label' => __('Huisnummer', 'tempel-settings'), 'value' => $huisnummer, 'class' => 'gfbag-huisnummer', 'placeholder' => $this->houseNumberPlaceholder),
            array('index' => 3, 'label' => __('Toevoeging', 'tempel-settings'), 'value' => $toevoeging, 'class' => 'gfbag-toevoeging', 'placeholder' => $this->additionPlaceholder, 'optional' => true),
            array('index' => 4, 'label' => __('Straat', 'tempel-settings'), 'value' => $straat, 'class' => 'gfbag-straat', 'placeholder' => $this->streetPlaceholder, 'readonly' => !$manual_input),
            array('index' => 5, 'label' => __('Plaats', 'tempel-settings'), 'value' => $plaats, 'class' => 'gfbag-plaats', 'placeholder' => $this->cityPlaceholder, 'readonly' => !$manual_input),
        );

        $markup = array();
        $markup[] = '<div class="ginput_complex ginput_container gfbag-address-lookup" id="' . esc_attr($field_id) . '" data-js-field-id="' . esc_attr($field_id) . '">';

        foreach ($inputs as $input) {
            $readonly = !empty($input['readonly']) ? "readonly='readonly'" : '';
            $readonly_class = !empty($input['readonly']) ? ' gfbag-readonly' : '';
            $autocomplete = $input['index'] <= 3 ? 'autocomplete="off"' : 'autocomplete="address-line1"';
            $input_id = $this->id . '.' . $input['index'];
            $required = $this->isRequired && in_array($input_id, $required_inputs, true) ? 'aria-required="true"' : '';
            $optional_attr = !empty($input['optional']) ? 'data-gfbag-optional="true" aria-required="false" aria-invalid="false"' : '';

            $markup[] = sprintf(
                '<span class="ginput_full %1$s"><label for="%2$s_%3$d"%14$s>%4$s</label><input type="text" name="input_%5$d.%3$d" id="%2$s_%3$d" value="%6$s" class="%1$s%12$s" placeholder="%7$s" %8$s %9$s %10$s %11$s %13$s /></span>',
                esc_attr($input['class']),
                esc_attr($field_id),
                (int) $input['index'],
                esc_html($input['label']),
                $id,
                esc_attr($input['value']),
                esc_attr($input['placeholder']),
                $readonly,
                $disabled,
                $tabindex,
                $autocomplete . ' ' . $required,
                esc_attr($readonly_class),
                $optional_attr,
                $sub_label_class
            );
        }

        $markup[] = '<div class="gfbag-message" id="' . esc_attr($message_id) . '" aria-live="polite"></div>';
        $markup[] = '</div>';

        return implode('', $markup);
    }

    private function get_form_editor_preview(): string
    {
        $sub_label_class = $this->hide_sub_labels() ? ' class="gfbag-visually-hidden"' : '';
        $inputs = array(
            array('label' => __('Postcode', 'tempel-settings'), 'placeholder' => $this->postcodePlaceholder, 'class' => 'gfbag-postcode'),
            array('label' => __('Huisnummer', 'tempel-settings'), 'placeholder' => $this->houseNumberPlaceholder, 'class' => 'gfbag-huisnummer'),
            array('label' => __('Toevoeging', 'tempel-settings'), 'placeholder' => $this->additionPlaceholder, 'class' => 'gfbag-toevoeging'),
            array('label' => __('Straat', 'tempel-settings'), 'placeholder' => $this->streetPlaceholder, 'class' => 'gfbag-straat'),
            array('label' => __('Plaats', 'tempel-settings'), 'placeholder' => $this->cityPlaceholder, 'class' => 'gfbag-plaats'),
        );

        $markup = array();
        $markup[] = '<div class="ginput_complex ginput_container gfbag-address-lookup gfbag-address-lookup-preview">';

        foreach ($inputs as $input) {
            $markup[] = sprintf(
                '<span class="ginput_full %1$s"><label%4$s>%2$s</label><input type="text" value="" placeholder="%3$s" disabled="disabled" /></span>',
                esc_attr($input['class']),
                esc_html($input['label']),
                esc_attr($input['placeholder']),
                $sub_label_class
            );
        }

        $markup[] = '</div>';

        return implode('', $markup);
    }

    public function get_field_label_class()
    {
        $class = parent::get_field_label_class();

        if ($this->hide_field_label()) {
            $class .= ' gfbag-visually-hidden';
        }

        return $class;
    }

    public function get_required_inputs_ids()
    {
        return array($this->id . '.1', $this->id . '.2');
    }

    public function is_value_submission_empty($form_id)
    {
        $postcode = trim((string) rgpost('input_' . $this->id . '_1'));
        $huisnummer = trim((string) rgpost('input_' . $this->id . '_2'));

        return $postcode === '' || $huisnummer === '';
    }

    public function validate($value, $form)
    {
        $postcode = is_array($value) ? trim((string) rgget($this->id . '.1', $value)) : '';
        $huisnummer = is_array($value) ? trim((string) rgget($this->id . '.2', $value)) : '';
        $straat = is_array($value) ? trim((string) rgget($this->id . '.4', $value)) : '';
        $plaats = is_array($value) ? trim((string) rgget($this->id . '.5', $value)) : '';

        if ($this->isRequired && ($postcode === '' || $huisnummer === '')) {
            $this->failed_validation = true;
            $this->validation_message = $this->errorMessage ?: $this->get_required_address_message();
            return;
        }

        if (($postcode !== '' || $huisnummer !== '') && ($straat === '' || $plaats === '')) {
            $this->failed_validation = true;
            $this->validation_message = $this->errorMessage ?: $this->get_incomplete_address_message();
        }
    }

    public function get_value_entry_detail($value, $currency = '', $use_text = false, $format = 'html', $media = 'screen')
    {
        if (!is_array($value)) {
            return '';
        }

        $parts = array_filter(array(
            rgget($this->id . '.4', $value),
            trim(implode(' ', array_filter(array(rgget($this->id . '.2', $value), rgget($this->id . '.3', $value))))),
            rgget($this->id . '.1', $value),
            rgget($this->id . '.5', $value),
        ));

        return esc_html(implode(', ', $parts));
    }

    public function get_form_editor_inline_script_on_page_render()
    {
        return "function SetDefaultValues_nl_address_lookup(field){field.label='Adres';field.manualInput=false;field.hideFieldLabel=false;field.hideSubLabels=false;field.postcodePlaceholder='Postcode';field.houseNumberPlaceholder='Huisnr';field.additionPlaceholder='Toev.';field.streetPlaceholder='Straat';field.cityPlaceholder='Plaats';field.loadingMessage='';field.notFoundMessage='';field.requiredAddressMessage='';field.incompleteAddressMessage='';return field;}";
    }

    public function get_entry_inputs()
    {
        return array(
            array('id' => $this->id . '.1', 'label' => __('Postcode', 'tempel-settings')),
            array('id' => $this->id . '.2', 'label' => __('Huisnummer', 'tempel-settings')),
            array('id' => $this->id . '.3', 'label' => __('Toevoeging', 'tempel-settings')),
            array('id' => $this->id . '.4', 'label' => __('Straat', 'tempel-settings')),
            array('id' => $this->id . '.5', 'label' => __('Plaats', 'tempel-settings')),
        );
    }

    public static function add_manual_input_setting($position, $form_id): void
    {
        if ($position !== 25) {
            return;
        }

        ?>
        <li class="tempel_manual_input_setting field_setting">
            <input
                    type="checkbox"
                    id="tempel_manual_input"
                    onclick="SetFieldProperty('manualInput', this.checked);"
            >
            <label for="tempel_manual_input" class="inline">
                <?php esc_html_e('Handmatige invoer straat en plaats toestaan', 'tempel-settings'); ?>
            </label>
        </li>
        <?php
    }

    public static function add_address_messages_setting($position, $form_id): void
    {
        if ($position !== 25) {
            return;
        }

        ?>
        <li class="tempel_address_messages_setting field_setting">
            <label for="tempel_loading_message">
                <?php esc_html_e('Tekst tijdens laden', 'tempel-settings'); ?>
            </label>
            <input
                    type="text"
                    id="tempel_loading_message"
                    class="fieldwidth-3"
                    placeholder="<?php echo esc_attr__('We zoeken je adres...', 'tempel-settings'); ?>"
                    oninput="SetFieldProperty('loadingMessage', this.value);"
            >

            <label for="tempel_not_found_message">
                <?php esc_html_e('Tekst bij geen adres/API-fout', 'tempel-settings'); ?>
            </label>
            <input
                    type="text"
                    id="tempel_not_found_message"
                    class="fieldwidth-3"
                    placeholder="<?php echo esc_attr__('Geen adres gevonden.', 'tempel-settings'); ?>"
                    oninput="SetFieldProperty('notFoundMessage', this.value);"
            >

            <label for="tempel_required_address_message">
                <?php esc_html_e('Validatie: postcode en huisnummer verplicht', 'tempel-settings'); ?>
            </label>
            <input
                    type="text"
                    id="tempel_required_address_message"
                    class="fieldwidth-3"
                    placeholder="<?php echo esc_attr__('Vul je postcode en huisnummer in.', 'tempel-settings'); ?>"
                    oninput="SetFieldProperty('requiredAddressMessage', this.value);"
            >

            <label for="tempel_incomplete_address_message">
                <?php esc_html_e('Validatie: adres niet compleet', 'tempel-settings'); ?>
            </label>
            <input
                    type="text"
                    id="tempel_incomplete_address_message"
                    class="fieldwidth-3"
                    placeholder="<?php echo esc_attr__('Controleer je adresgegevens. We kunnen het adres nog niet compleet maken.', 'tempel-settings'); ?>"
                    oninput="SetFieldProperty('incompleteAddressMessage', this.value);"
            >
        </li>
        <?php
    }

    public static function add_display_setting($position, $form_id): void
    {
        if ($position !== 25) {
            return;
        }

        ?>
        <li class="tempel_address_display_setting field_setting">
            <input
                    type="checkbox"
                    id="tempel_hide_field_label"
                    onclick="SetFieldProperty('hideFieldLabel', this.checked);"
            >
            <label for="tempel_hide_field_label" class="inline">
                <?php esc_html_e('Hoofdlabel verbergen', 'tempel-settings'); ?>
            </label>

            <br>

            <input
                    type="checkbox"
                    id="tempel_hide_sub_labels"
                    onclick="SetFieldProperty('hideSubLabels', this.checked);"
            >
            <label for="tempel_hide_sub_labels" class="inline">
                <?php esc_html_e('Labels van adresvelden verbergen', 'tempel-settings'); ?>
            </label>
        </li>
        <?php
    }

    public static function add_placeholders_setting($position, $form_id): void
    {
        if ($position !== 25) {
            return;
        }

        ?>
        <li class="tempel_address_placeholders_setting field_setting">
            <label for="tempel_postcode_placeholder">
                <?php esc_html_e('Placeholder postcode', 'tempel-settings'); ?>
            </label>
            <input type="text" id="tempel_postcode_placeholder" class="fieldwidth-3" oninput="SetFieldProperty('postcodePlaceholder', this.value);">

            <label for="tempel_house_number_placeholder">
                <?php esc_html_e('Placeholder huisnummer', 'tempel-settings'); ?>
            </label>
            <input type="text" id="tempel_house_number_placeholder" class="fieldwidth-3" oninput="SetFieldProperty('houseNumberPlaceholder', this.value);">

            <label for="tempel_addition_placeholder">
                <?php esc_html_e('Placeholder toevoeging', 'tempel-settings'); ?>
            </label>
            <input type="text" id="tempel_addition_placeholder" class="fieldwidth-3" oninput="SetFieldProperty('additionPlaceholder', this.value);">

            <label for="tempel_street_placeholder">
                <?php esc_html_e('Placeholder straat', 'tempel-settings'); ?>
            </label>
            <input type="text" id="tempel_street_placeholder" class="fieldwidth-3" oninput="SetFieldProperty('streetPlaceholder', this.value);">

            <label for="tempel_city_placeholder">
                <?php esc_html_e('Placeholder plaats', 'tempel-settings'); ?>
            </label>
            <input type="text" id="tempel_city_placeholder" class="fieldwidth-3" oninput="SetFieldProperty('cityPlaceholder', this.value);">
        </li>
        <?php
    }

    public static function add_editor_script(): void
    {
        ?>
        <script>
            if (typeof fieldSettings !== 'undefined') {
                fieldSettings.nl_address_lookup = (fieldSettings.nl_address_lookup || '') + ', .tempel_manual_input_setting, .tempel_address_display_setting, .tempel_address_placeholders_setting, .tempel_address_messages_setting';
            }

            jQuery(document).on('gform_load_field_settings', function (event, field) {
                jQuery('#tempel_manual_input').prop('checked', !!field.manualInput);
                jQuery('#tempel_hide_field_label').prop('checked', !!field.hideFieldLabel);
                jQuery('#tempel_hide_sub_labels').prop('checked', !!field.hideSubLabels);
                jQuery('#tempel_postcode_placeholder').val(field.postcodePlaceholder || '');
                jQuery('#tempel_house_number_placeholder').val(field.houseNumberPlaceholder || '');
                jQuery('#tempel_addition_placeholder').val(field.additionPlaceholder || '');
                jQuery('#tempel_street_placeholder').val(field.streetPlaceholder || '');
                jQuery('#tempel_city_placeholder').val(field.cityPlaceholder || '');
                jQuery('#tempel_loading_message').val(field.loadingMessage || '');
                jQuery('#tempel_not_found_message').val(field.notFoundMessage || '');
                jQuery('#tempel_required_address_message').val(field.requiredAddressMessage || '');
                jQuery('#tempel_incomplete_address_message').val(field.incompleteAddressMessage || '');
            });
        </script>
        <?php
    }

    private function is_manual_input_enabled(): bool
    {
        return filter_var($this->manualInput, FILTER_VALIDATE_BOOLEAN);
    }

    private function hide_field_label(): bool
    {
        return filter_var($this->hideFieldLabel, FILTER_VALIDATE_BOOLEAN);
    }

    private function hide_sub_labels(): bool
    {
        return filter_var($this->hideSubLabels, FILTER_VALIDATE_BOOLEAN);
    }

    private function enqueue_address_styles(): void
    {
        wp_enqueue_style(
            'tmpl-gf-bag-address-frontend',
            TEMPEL_SETTINGS_ASSET_URL . 'css/gf-bag-address.css',
            array(),
            TEMPEL_SETTINGS_VERSION
        );
    }

    private function get_address_messages(): array
    {
        return array(
            'loading' => $this->get_loading_message(),
            'notFound' => $this->get_not_found_message(),
        );
    }

    private function get_loading_message(): string
    {
        return trim((string) $this->loadingMessage) ?: __('We zoeken je adres...', 'tempel-settings');
    }

    private function get_not_found_message(): string
    {
        return trim((string) $this->notFoundMessage) ?: __('Geen adres gevonden.', 'tempel-settings');
    }

    private function get_required_address_message(): string
    {
        return trim((string) $this->requiredAddressMessage) ?: __('Vul je postcode en huisnummer in.', 'tempel-settings');
    }

    private function get_incomplete_address_message(): string
    {
        return trim((string) $this->incompleteAddressMessage) ?: __('Controleer je adresgegevens. We kunnen het adres nog niet compleet maken.', 'tempel-settings');
    }
}

add_action('gform_field_standard_settings', array('GF_Field_NL_Address', 'add_manual_input_setting'), 10, 2);
add_action('gform_field_standard_settings', array('GF_Field_NL_Address', 'add_display_setting'), 10, 2);
add_action('gform_field_standard_settings', array('GF_Field_NL_Address', 'add_placeholders_setting'), 10, 2);
add_action('gform_field_standard_settings', array('GF_Field_NL_Address', 'add_address_messages_setting'), 10, 2);
add_action('gform_editor_js', array('GF_Field_NL_Address', 'add_editor_script'));
