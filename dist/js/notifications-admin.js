(function ($) {
    'use strict';

    var tableBody = $('#the-list');

    if (!tableBody.length) {
        return;
    }

    tableBody.sortable({
        axis: 'y',
        cursor: 'move',
        items: '> tr',
        opacity: 0.7,
        update: function () {
            var postIds = tableBody.sortable('toArray').map(function (rowId) {
                return rowId.replace('post-', '');
            });

            function handleError() {
                window.alert(tempelNotificationsAdmin.error);
                window.location.reload();
            }

            $.post(tempelNotificationsAdmin.ajaxUrl, {
                action: 'tempel_sort_notifications',
                nonce: tempelNotificationsAdmin.nonce,
                post_ids: postIds
            }).done(function (response) {
                if (!response || response.success !== true) {
                    handleError();
                }
            }).fail(handleError);
        }
    });

    tableBody.on('click', '.tempel-notification-toggle', function () {
        var button = $(this);

        button.prop('disabled', true);
        $.post(tempelNotificationsAdmin.ajaxUrl, {
            action: 'tempel_toggle_notification',
            nonce: tempelNotificationsAdmin.toggleNonce,
            post_id: button.data('post-id')
        }).done(function (response) {
            if (!response || response.success !== true) {
                window.alert(tempelNotificationsAdmin.toggleError);
                return;
            }

            var active = response.data.active === true;
            button.toggleClass('is-active', active);
            button.attr('aria-pressed', active ? 'true' : 'false');
            button.attr('aria-label', active ? tempelNotificationsAdmin.deactivate : tempelNotificationsAdmin.activate);
        }).fail(function () {
            window.alert(tempelNotificationsAdmin.toggleError);
        }).always(function () {
            button.prop('disabled', false);
        });
    });
}(jQuery));
