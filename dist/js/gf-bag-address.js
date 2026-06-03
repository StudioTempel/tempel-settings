(function ($) {
    function setMessage($field, message, type) {
        var $message = $field.find('.gfbag-message');
        $message.removeClass('is-error is-loading is-success');

        if (!message) {
            $message.text('');
            return;
        }

        $message.addClass('is-' + type).text(message);
    }

    function shouldLookup(values) {
        return values.postcode !== '' && values.huisnummer !== '';
    }

    function lookupAddress($field) {
        var fieldId = $field.data('js-field-id');
        var config = window.GFBagAddressFieldData && window.GFBagAddressFieldData[fieldId];

        if (!config) {
            return;
        }

        var values = {
            postcode: $.trim($field.find('.gfbag-postcode input').val() || ''),
            huisnummer: $.trim($field.find('.gfbag-huisnummer input').val() || ''),
            toevoeging: $.trim($field.find('.gfbag-toevoeging input').val() || '')
        };

        if (!shouldLookup(values)) {
            return;
        }

        setMessage($field, config.messages.loading, 'loading');

        $.post(config.ajaxUrl, {
            action: config.action,
            nonce: config.nonce,
            postcode: values.postcode,
            huisnummer: values.huisnummer,
            toevoeging: values.toevoeging
        }).done(function (response) {
            if (!response || !response.success || !response.data) {
                setMessage($field, config.messages.notFound, 'error');
                return;
            }

            $field.find('.gfbag-postcode input').val(response.data.postcode || values.postcode);
            $field.find('.gfbag-huisnummer input').val(response.data.huisnummer || values.huisnummer);
            $field.find('.gfbag-toevoeging input').val(response.data.toevoeging || values.toevoeging);
            $field.find('.gfbag-straat input').val(response.data.straat || '');
            $field.find('.gfbag-plaats input').val(response.data.plaats || '');
            $field.find('.gfbag-oppervlakte input').val(response.data.oppervlakte || '');
            $field.find('.gfbag-bouwjaar input').val(response.data.bouwjaar || '');
            $field.find('.gfbag-gebruiksdoelen input').val(response.data.gebruiksdoelen || '');

            setMessage($field, '', 'success');
        }).fail(function (xhr) {
            var message = config.messages.notFound;

            if (xhr.responseJSON && xhr.responseJSON.data && xhr.responseJSON.data.message) {
                message = xhr.responseJSON.data.message;
            }

            $field.find('.gfbag-straat input, .gfbag-plaats input, .gfbag-oppervlakte input, .gfbag-bouwjaar input, .gfbag-gebruiksdoelen input').val('');
            setMessage($field, message, 'error');
        });
    }

    $(document).on('blur', '.gfbag-address-lookup .gfbag-postcode input, .gfbag-address-lookup .gfbag-huisnummer input, .gfbag-address-lookup .gfbag-toevoeging input', function () {
        lookupAddress($(this).closest('.gfbag-address-lookup'));
    });
})(jQuery);
