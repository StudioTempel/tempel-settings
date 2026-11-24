jQuery(document).ready(function($) {
    var saveStatus = document.createElement('div');
    var saveStatusHideTimer = 0;
    saveStatus.className = 'tempel-live-save-status';
    saveStatus.setAttribute('role', 'status');
    saveStatus.setAttribute('aria-live', 'polite');
    saveStatus.hidden = true;
    document.body.appendChild(saveStatus);

    function showSaveStatus(state, message, duration) {
        window.clearTimeout(saveStatusHideTimer);
        saveStatus.hidden = false;
        saveStatus.className = 'tempel-live-save-status is-' + state;
        saveStatus.textContent = message;
        window.requestAnimationFrame(function () { saveStatus.classList.add('is-visible'); });
        if (duration) {
            saveStatusHideTimer = window.setTimeout(function () {
                saveStatus.classList.remove('is-visible');
                window.setTimeout(function () { saveStatus.hidden = true; }, 180);
            }, duration);
        }
    }

    window.tempelShowSaveStatus = showSaveStatus;

    function showSavedNotice(notice) {
        if (!notice || notice.dataset.tempelSaveToast === 'shown') return;
        var message = (notice.textContent || '').replace(/\s+/g, ' ').trim();
        if (/opgeslagen|instellingen\s+(?:zijn\s+)?bijgewerkt|saved|settings\s+updated/i.test(message)) {
            notice.dataset.tempelSaveToast = 'shown';
            showSaveStatus('saved', 'Opgeslagen', 3000);
        }
    }

    document.querySelectorAll('.tempel-ui-page .notice-success, .tempel-ui-page .updated, .tempel-ui-page #setting-error-settings_updated').forEach(showSavedNotice);
    if (typeof window.MutationObserver === 'function') {
        new MutationObserver(function (mutations) {
            mutations.forEach(function (mutation) {
                mutation.addedNodes.forEach(function (node) {
                    if (!node || node.nodeType !== 1) return;
                    if (node.matches && node.matches('.notice-success, .updated, #setting-error-settings_updated')) showSavedNotice(node);
                    if (node.querySelectorAll) node.querySelectorAll('.notice-success, .updated, #setting-error-settings_updated').forEach(showSavedNotice);
                });
            });
        }).observe(document.querySelector('.tempel-ui-page') || document.body, { childList: true, subtree: true });
    }

    function initLiveSettingsSave() {
        var forms = Array.prototype.filter.call(document.querySelectorAll('.tempel-ui-page form'), function (form) {
            try {
                var actionUrl = new URL(form.getAttribute('action') || '', window.location.href);
                return actionUrl.pathname.replace(/\/+$/, '').endsWith('/options.php') && form.querySelector('input[name="option_page"]');
            } catch (error) {
                return false;
            }
        });

        if (!forms.length || typeof window.fetch !== 'function') return;

        forms.forEach(function (form) {
            var actionUrl = new URL(form.getAttribute('action') || '', window.location.href).href;
            var timer = 0;
            var requestNumber = 0;
            var dirty = false;
            var saving = false;
            var pending = false;
            form.classList.add('tempel-autosave-form');

            function setStatus(state, message) {
                showSaveStatus(state, message, state === 'saved' ? 3000 : 0);
            }

            function save() {
                window.clearTimeout(timer);
                if (!dirty) return Promise.resolve();
                if (saving) {
                    pending = true;
                    return Promise.resolve();
                }
                dirty = false;
                saving = true;
                pending = false;
                var currentRequest = ++requestNumber;
                var data = new FormData(form);
                setStatus('saving', 'Opslaan…');

                return window.fetch(actionUrl, {
                    method: 'POST',
                    body: data,
                    credentials: 'same-origin',
                    redirect: 'follow',
                    headers: { 'X-Tempel-Autosave': '1' }
                }).then(function (response) {
                    if (!response.ok || /wp-login\.php/.test(response.url)) throw new Error('save_failed');
                    if (currentRequest === requestNumber) setStatus('saved', 'Opgeslagen');
                }).catch(function () {
                    if (currentRequest === requestNumber) setStatus('error', 'Kon niet opslaan');
                }).then(function () {
                    saving = false;
                    if (pending || dirty) {
                        dirty = true;
                        timer = window.setTimeout(save, 80);
                    }
                });
            }

            function schedule(event) {
                var field = event.target;
                if (!field || !field.name || field.disabled || /^(?:submit|button|file)$/i.test(field.type || '')) return;
                dirty = true;
                window.clearTimeout(timer);
                var immediate = event.type === 'change' || /^(?:checkbox|radio|select-one|select-multiple)$/i.test(field.type || '');
                timer = window.setTimeout(save, immediate ? 80 : 700);
            }

            form.addEventListener('input', schedule);
            form.addEventListener('change', schedule);
            form.addEventListener('submit', function (event) {
                var submitter = event.submitter;
                if (submitter && (submitter.formAction !== actionUrl || submitter.name === 'action')) return;
                event.preventDefault();
                dirty = true;
                save();
            });

            window.addEventListener('pagehide', function () {
                if ((!dirty && !saving) || !navigator.sendBeacon) return;
                window.clearTimeout(timer);
                navigator.sendBeacon(actionUrl, new FormData(form));
                dirty = false;
            });
        });
    }

    initLiveSettingsSave();

    document.querySelectorAll('#tmpl_templeton_settings textarea').forEach(function (field) {
        function resizeTempletonField() {
            field.style.height = 'auto';
            field.style.height = Math.max(field.scrollHeight + 2, 150) + 'px';
        }
        resizeTempletonField();
        field.addEventListener('input', resizeTempletonField);
    });

    function syncPostTypeConversionFields() {
        const enabled = $('#conversion_include_post_type').is(':checked');
        $('.tempel-post-type-dependent').prop('hidden', !enabled).attr('aria-hidden', enabled ? 'false' : 'true');
    }

    if ($('#conversion_include_post_type').length) {
        $('#conversion_include_post_type').on('change', syncPostTypeConversionFields);
        syncPostTypeConversionFields();
    }

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
