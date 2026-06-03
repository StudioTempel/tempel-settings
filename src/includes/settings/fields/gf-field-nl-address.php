<?php

if (!class_exists('GFForms') || !class_exists('GF_Field')) {
    return;
}

class GF_Field_NL_Address extends GF_Field
{
    public $type = 'nl_address_lookup';

    public function get_form_editor_field_title()
    {
        return esc_attr__('Adres NL (BAG)', 'tempel-settings');
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
            return '<div class="ginput_container">' . esc_html__('De adreszoeker wordt op de frontend getoond.', 'tempel-settings') . '</div>';
        }

        $field_id = $is_entry_detail || $form_id === 0 ? "input_{$id}" : "input_{$form_id}_{$id}";
        $tabindex = $this->get_tabindex();
        $disabled = $is_entry_detail ? "disabled='disabled'" : '';
        $required = $this->isRequired ? 'aria-required="true"' : '';

        $values = is_array($value) ? $value : array();
        $postcode = esc_attr(rgget($this->id . '.1', $values));
        $huisnummer = esc_attr(rgget($this->id . '.2', $values));
        $toevoeging = esc_attr(rgget($this->id . '.3', $values));
        $straat = esc_attr(rgget($this->id . '.4', $values));
        $plaats = esc_attr(rgget($this->id . '.5', $values));
        $oppervlakte = esc_attr(rgget($this->id . '.6', $values));
        $bouwjaar = esc_attr(rgget($this->id . '.7', $values));
        $gebruiksdoelen = esc_attr(rgget($this->id . '.8', $values));

        wp_enqueue_script(
            'tmpl-gf-bag-address-frontend',
            TEMPEL_SETTINGS_ASSET_URL . 'js/gf-bag-address.js',
            array('jquery'),
            TEMPEL_SETTINGS_VERSION,
            true
        );

        wp_enqueue_style(
            'tmpl-gf-bag-address-frontend',
            TEMPEL_SETTINGS_ASSET_URL . 'css/gf-bag-address.css',
            array(),
            TEMPEL_SETTINGS_VERSION
        );

        $script_handle = 'tmpl-gf-bag-address-field-' . $form_id . '-' . $id;
        wp_register_script($script_handle, '', array('tmpl-gf-bag-address-frontend'), TEMPEL_SETTINGS_VERSION, true);
        wp_enqueue_script($script_handle);
        wp_add_inline_script(
            $script_handle,
            'window.GFBagAddressFieldData = window.GFBagAddressFieldData || {}; window.GFBagAddressFieldData["' . esc_js($field_id) . '"] = ' . wp_json_encode(\Tempel\GF_BAG_Address::get_ajax_config($id)) . ';',
            'before'
        );

        $message_id = $field_id . '_message';

        $inputs = array(
            array('index' => 1, 'label' => __('Postcode', 'tempel-settings'), 'value' => $postcode, 'class' => 'gfbag-postcode', 'placeholder' => '1234AB'),
            array('index' => 2, 'label' => __('Huisnummer', 'tempel-settings'), 'value' => $huisnummer, 'class' => 'gfbag-huisnummer', 'placeholder' => '10'),
            array('index' => 3, 'label' => __('Toevoeging', 'tempel-settings'), 'value' => $toevoeging, 'class' => 'gfbag-toevoeging', 'placeholder' => 'A'),
            array('index' => 4, 'label' => __('Straat', 'tempel-settings'), 'value' => $straat, 'class' => 'gfbag-straat', 'placeholder' => '', 'readonly' => true),
            array('index' => 5, 'label' => __('Plaats', 'tempel-settings'), 'value' => $plaats, 'class' => 'gfbag-plaats', 'placeholder' => '', 'readonly' => true),
            array('index' => 6, 'label' => __('Oppervlakte (m2)', 'tempel-settings'), 'value' => $oppervlakte, 'class' => 'gfbag-oppervlakte', 'placeholder' => '', 'readonly' => true),
            array('index' => 7, 'label' => __('Bouwjaar', 'tempel-settings'), 'value' => $bouwjaar, 'class' => 'gfbag-bouwjaar', 'placeholder' => '', 'readonly' => true),
            array('index' => 8, 'label' => __('Gebruiksdoelen', 'tempel-settings'), 'value' => $gebruiksdoelen, 'class' => 'gfbag-gebruiksdoelen', 'placeholder' => '', 'readonly' => true),
        );

        $markup = array();
        $markup[] = '<div class="ginput_complex ginput_container gfbag-address-lookup" id="' . esc_attr($field_id) . '" data-js-field-id="' . esc_attr($field_id) . '">';

        foreach ($inputs as $input) {
            $readonly = !empty($input['readonly']) ? "readonly='readonly'" : '';
            $autocomplete = $input['index'] <= 3 ? 'autocomplete="off"' : 'autocomplete="address-line1"';

            $markup[] = sprintf(
                '<span class="ginput_full %1$s"><label for="%2$s_%3$d">%4$s</label><input type="text" name="input_%5$d.%3$d" id="%2$s_%3$d" value="%6$s" class="%1$s" placeholder="%7$s" %8$s %9$s %10$s %11$s /></span>',
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
                $autocomplete . ' ' . $required
            );
        }

        $markup[] = '<div class="gfbag-message" id="' . esc_attr($message_id) . '" aria-live="polite"></div>';
        $markup[] = '</div>';

        return implode('', $markup);
    }

    public function get_field_content($value, $force_frontend_label, $form)
    {
        $is_form_editor = $this->is_form_editor();
        $admin_buttons = $this->get_admin_buttons();
        $description = $this->get_description($this->description, 'gfield_description');
        $label_id = 'field_' . $form['id'] . '_' . $this->id;

        if ($is_form_editor) {
            return sprintf('%s<label class="gfield_label gform-field-label">%s</label>{FIELD_CONTENT}', $admin_buttons, esc_html($this->get_field_label(false, $value)));
        }

        if ($this->label === '') {
            return "{FIELD_CONTENT}{$description}";
        }

        $legend = $this->isRequired
            ? sprintf('%s<span class="gfield_required"><span class="screen-reader-text">%s</span>*</span>', esc_html($this->get_field_label($force_frontend_label, $value)), esc_html__('Required', 'gravityforms'))
            : esc_html($this->get_field_label($force_frontend_label, $value));

        if ($this->descriptionPlacement === 'above') {
            return sprintf('<fieldset id="%s" class="gfield_fieldset"><legend class="gfield_label gform-field-label">%s</legend>%s{FIELD_CONTENT}</fieldset>', esc_attr($label_id), $legend, $description);
        }

        return sprintf('<fieldset id="%s" class="gfield_fieldset"><legend class="gfield_label gform-field-label">%s</legend>{FIELD_CONTENT}%s</fieldset>', esc_attr($label_id), $legend, $description);
    }

    public function get_required_inputs_ids()
    {
        return array($this->id . '.1', $this->id . '.2');
    }

    public function validate($value, $form)
    {
        $postcode = is_array($value) ? trim((string) rgget($this->id . '.1', $value)) : '';
        $huisnummer = is_array($value) ? trim((string) rgget($this->id . '.2', $value)) : '';
        $straat = is_array($value) ? trim((string) rgget($this->id . '.4', $value)) : '';
        $plaats = is_array($value) ? trim((string) rgget($this->id . '.5', $value)) : '';

        if ($this->isRequired && ($postcode === '' || $huisnummer === '')) {
            $this->failed_validation = true;
            $this->validation_message = $this->errorMessage ?: __('Postcode en huisnummer zijn verplicht.', 'tempel-settings');
            return;
        }

        if (($postcode !== '' || $huisnummer !== '') && ($straat === '' || $plaats === '')) {
            $this->failed_validation = true;
            $this->validation_message = $this->errorMessage ?: __('Het adres kon niet worden opgehaald. Controleer postcode, huisnummer en toevoeging.', 'tempel-settings');
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
        return "function SetDefaultValues_nl_address_lookup(field){field.label='Adres';}";
    }

    public function get_entry_inputs()
    {
        return array(
            array('id' => $this->id . '.1', 'label' => __('Postcode', 'tempel-settings')),
            array('id' => $this->id . '.2', 'label' => __('Huisnummer', 'tempel-settings')),
            array('id' => $this->id . '.3', 'label' => __('Toevoeging', 'tempel-settings')),
            array('id' => $this->id . '.4', 'label' => __('Straat', 'tempel-settings')),
            array('id' => $this->id . '.5', 'label' => __('Plaats', 'tempel-settings')),
            array('id' => $this->id . '.6', 'label' => __('Oppervlakte', 'tempel-settings')),
            array('id' => $this->id . '.7', 'label' => __('Bouwjaar', 'tempel-settings')),
            array('id' => $this->id . '.8', 'label' => __('Gebruiksdoelen', 'tempel-settings')),
        );
    }
}
