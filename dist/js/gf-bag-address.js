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

    function normalizePostcode(postcode) {
        return postcode.replace(/\s+/g, '').toUpperCase();
    }

    function getPostcodeError(postcode, messages) {
        var normalized = normalizePostcode(postcode);

        if (normalized.length > 0 && normalized.length < 6) {
            return messages.postcodeTooShort || 'Vul een geldige Nederlandse postcode in, bijvoorbeeld 1234 AB.';
        }

        if (normalized.length >= 6 && !/^[1-9][0-9]{3}[A-Z]{2}$/.test(normalized)) {
            return messages.invalidPostcode || 'Vul een geldige Nederlandse postcode in, bijvoorbeeld 1234 AB.';
        }

        return '';
    }

    function getLookupKey(values) {
        return [
            normalizePostcode(values.postcode),
            values.huisnummer.toLowerCase(),
            values.toevoeging.toLowerCase()
        ].join('|');
    }

    function getFieldValues($field) {
        return {
            postcode: $.trim($field.find('.gfbag-postcode input').val() || ''),
            huisnummer: $.trim($field.find('.gfbag-huisnummer input').val() || ''),
            toevoeging: $.trim($field.find('.gfbag-toevoeging input').val() || '')
        };
    }

    function getErrorMessage(xhr, fallback) {
        var message = '';

        if (xhr.responseJSON) {
            if (xhr.responseJSON.message) {
                message = xhr.responseJSON.message;
            }

            if (!message && xhr.responseJSON.data && xhr.responseJSON.data.message) {
                message = xhr.responseJSON.data.message;
            }
        }

        if (message === 'Resource not found') {
            return fallback;
        }

        return message || fallback;
    }

    function requestAddress(config, values) {
        var payload = {
            postcode: values.postcode,
            huisnummer: values.huisnummer,
            toevoeging: values.toevoeging
        };

        if (config.restUrl) {
            return $.ajax({
                url: config.restUrl,
                method: 'POST',
                data: payload
            }).then(function (response) {
                return {
                    success: true,
                    data: response
                };
            }, function (xhr) {
                if (xhr.status && xhr.status !== 404) {
                    return $.Deferred().reject(xhr).promise();
                }

                return requestAddressViaAjax(config, values);
            });
        }

        return requestAddressViaAjax(config, values);
    }

    function requestAddressViaAjax(config, values) {
        return $.post(config.ajaxUrl, {
            action: config.action,
            nonce: config.nonce,
            postcode: values.postcode,
            huisnummer: values.huisnummer,
            toevoeging: values.toevoeging
        });
    }

    function lookupAddress($field) {
        var fieldId = $field.data('js-field-id');
        var config = window.GFBagAddressFieldData && window.GFBagAddressFieldData[fieldId];

        if (!config) {
            return;
        }

        var values = getFieldValues($field);

        if (!shouldLookup(values)) {
            $field.removeData('gfbag-last-lookup-key gfbag-pending-lookup-key');
            return;
        }

        var postcodeError = getPostcodeError(values.postcode, config.messages || {});

        if (postcodeError) {
            if (!config.manualInput) {
                $field.find('.gfbag-straat input, .gfbag-plaats input').val('');
            }

            $field.removeData('gfbag-pending-lookup-key');
            setMessage($field, postcodeError, 'error');
            return;
        }

        var lookupKey = getLookupKey(values);

        if (
            lookupKey === $field.data('gfbag-last-lookup-key') ||
            lookupKey === $field.data('gfbag-pending-lookup-key')
        ) {
            return;
        }

        $field.data('gfbag-pending-lookup-key', lookupKey);
        setMessage($field, config.messages.loading, 'loading');

        requestAddress(config, values).done(function (response) {
            if (!response || !response.success || !response.data) {
                setMessage($field, config.messages.notFound, 'error');
                $field.data('gfbag-last-lookup-key', lookupKey);
                return;
            }

            $field.find('.gfbag-postcode input').val(response.data.postcode || values.postcode);
            $field.find('.gfbag-huisnummer input').val(response.data.huisnummer || values.huisnummer);
            $field.find('.gfbag-toevoeging input').val(response.data.toevoeging || values.toevoeging);
            $field.find('.gfbag-straat input').val(response.data.straat || '');
            $field.find('.gfbag-plaats input').val(response.data.plaats || '');

            $field.data('gfbag-last-lookup-key', getLookupKey(getFieldValues($field)));
            setMessage($field, '', 'success');
        }).fail(function (xhr) {
            var message = getErrorMessage(xhr, config.messages.notFound);

            if (!config.manualInput) {
                $field.find('.gfbag-straat input, .gfbag-plaats input').val('');
            }

            $field.data('gfbag-last-lookup-key', lookupKey);
            setMessage($field, message, 'error');
        }).always(function () {
            if ($field.data('gfbag-pending-lookup-key') === lookupKey) {
                $field.removeData('gfbag-pending-lookup-key');
            }
        });
    }

    $(document).on('blur', '.gfbag-address-lookup .gfbag-postcode input, .gfbag-address-lookup .gfbag-huisnummer input, .gfbag-address-lookup .gfbag-toevoeging input', function () {
        lookupAddress($(this).closest('.gfbag-address-lookup'));
    });
})(jQuery);
