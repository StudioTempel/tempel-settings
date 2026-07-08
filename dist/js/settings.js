jQuery(document).ready(function($) {
    if ($('#conversion_selected_forms').length) {
        $('#conversion_selected_forms').select2({
            placeholder: 'Selecteer formulieren',
            allowClear: true,
            multiple: true
        });
    }

    if ($('#tempel_mail_recipients').length && $.fn.select2) {
        $('#tempel_mail_recipients').select2({
            placeholder: 'Selecteer gebruikers',
            allowClear: true,
            multiple: true
        });
    }

    if ($('#status_safeupdate_day').length) {
        $('#status_safeupdate_day').select2({
            minimumResultsForSearch: -1,
            placeholder: 'Selecteer de dag waarop de safeupdate plaatsvind'
        });
    }

    if ($('#status_backup_interval').length) {
        $('#status_backup_interval').flatpickr({
            enableTime: true,
            dateFormat: 'H:i',
            time_24hr: true,
            noCalendar: true
        });
    }

    $('button#reset_status_last_checkup_date').on('click', function() {
        if (!confirm('Weet je zeker dat je de checkup wilt resetten?')) {
            return;
        }

        $.ajax({
            url: ajaxurl,
            type: 'POST',
            dataType: 'json',
            data: {
                action: 'reset_checkup',
                nonce: tmplWidgetSettings.nonce
            },
            success: function(response) {
                if (response.success && response.data.status_last_checkup_date) {
                    $('input[name="tmpl_widget_settings[status_last_checkup_date]"]').val(response.data.status_last_checkup_date);
                    alert('Checkup is gereset');
                    return;
                }

                alert('Checkup kon niet worden gereset');
            },
            error: function(error) {
                console.error(error);
                alert('Checkup kon niet worden gereset');
            }
        });
    });

    $('#tempel_test_postcode_api').on('click', function() {
        if (typeof tempelPostcodeApiTest === 'undefined') {
            return;
        }

        var $button = $(this);
        var $result = $('#tempel_postcode_api_test_result');
        var $status = $('[data-tempel-status="api_connection"]');

        $button.prop('disabled', true);
        $result.removeClass('is-success is-error').addClass('is-loading').text(tempelPostcodeApiTest.messages.testing);

        $.ajax({
            url: tempelPostcodeApiTest.ajaxUrl,
            type: 'POST',
            dataType: 'json',
            data: {
                action: 'tempel_test_postcode_api',
                nonce: tempelPostcodeApiTest.nonce,
                postcode: $('#tempel_postcode_test_postcode').val(),
                huisnummer: $('#tempel_postcode_test_huisnummer').val(),
                api_key: $('#gf_bag_address_api_key').val(),
                endpoint: $('#gf_bag_address_endpoint').val()
            },
            success: function(response) {
                var message = response && response.data && response.data.message
                    ? response.data.message
                    : tempelPostcodeApiTest.messages.error;

                if (response && response.success) {
                    $result.removeClass('is-loading is-error').addClass('is-success').text(message);
                    $status
                        .removeClass('tempel-health-status__item--neutral tempel-health-status__item--warning tempel-health-status__item--error')
                        .addClass('tempel-health-status__item--ok')
                        .find('.tempel-health-status__message')
                        .text('Verbinding werkt.');
                    return;
                }

                $result.removeClass('is-loading is-success').addClass('is-error').text(message);
                $status
                    .removeClass('tempel-health-status__item--neutral tempel-health-status__item--warning tempel-health-status__item--ok')
                    .addClass('tempel-health-status__item--error')
                    .find('.tempel-health-status__message')
                    .text(message);
            },
            error: function(xhr) {
                var message = xhr.responseJSON && xhr.responseJSON.data && xhr.responseJSON.data.message
                    ? xhr.responseJSON.data.message
                    : tempelPostcodeApiTest.messages.error;

                $result.removeClass('is-loading is-success').addClass('is-error').text(message);
                $status
                    .removeClass('tempel-health-status__item--neutral tempel-health-status__item--warning tempel-health-status__item--ok')
                    .addClass('tempel-health-status__item--error')
                    .find('.tempel-health-status__message')
                    .text(message);
            },
            complete: function() {
                $button.prop('disabled', false);
            }
        });
    });

    $(".category__header input[type='checkbox']").on('change', function() {
        $(this).closest('.settings__category').find('.category__content.content__collapsable').slideToggle();
    });
});
