jQuery(document).ready(function($) {
    if ($('#conversion_selected_forms').length) {
        $('#conversion_selected_forms').select2({
            placeholder: 'Selecteer formulieren',
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

    $(".category__header input[type='checkbox']").on('change', function() {
        $(this).closest('.settings__category').find('.category__content.content__collapsable').slideToggle();
    });
});
