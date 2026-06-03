jQuery(document).ready(function($) {
    $('.widget__content__dropdown').on('click', function() {
        $(this).toggleClass('active');
        $(this).find('.item__dropdown__value').slideToggle();
    });

    var $analyticsWidget = $('[data-tempel-analytics-widget]');

    if (!$analyticsWidget.length) {
        return;
    }

    var $visitors = $analyticsWidget.closest('#widget-analytics').find('[data-tempel-analytics-visitors]');
    var $message = $analyticsWidget.find('[data-tempel-analytics-message]');
    var $messageValue = $message.find('.item__value');

    function formatDate(date) {
        var month = ('0' + (date.getMonth() + 1)).slice(-2);
        var day = ('0' + date.getDate()).slice(-2);

        return date.getFullYear() + '-' + month + '-' + day;
    }

    function getMetricValue(response) {
        if (!response || typeof response !== 'object') {
            return null;
        }

        if (response.totals && response.totals[0] && response.totals[0].metricValues && response.totals[0].metricValues[0]) {
            return response.totals[0].metricValues[0].value;
        }

        if (response.rows && response.rows[0] && response.rows[0].metricValues && response.rows[0].metricValues[0]) {
            return response.rows[0].metricValues[0].value;
        }

        if (response.data) {
            return getMetricValue(response.data);
        }

        return null;
    }

    function showMessage(message) {
        $visitors.text('-');
        $messageValue.text(message);
        $message.removeAttr('hidden');
    }

    if (typeof tempelAnalyticsWidget === 'undefined') {
        showMessage($analyticsWidget.attr('data-tempel-analytics-error') || 'Visitors could not be retrieved.');
        return;
    }

    var endDate = new Date();
    var startDate = new Date();
    startDate.setDate(endDate.getDate() - 6);

    $.ajax({
        url: tempelAnalyticsWidget.endpoint,
        method: 'GET',
        dataType: 'json',
        timeout: 15000,
        data: {
            metrics: [
                {
                    name: 'totalUsers'
                }
            ],
            startDate: formatDate(startDate),
            endDate: formatDate(endDate)
        },
        beforeSend: function(xhr) {
            xhr.setRequestHeader('X-WP-Nonce', tempelAnalyticsWidget.nonce);
        }
    }).done(function(response) {
        if (response && response.message && response.code) {
            showMessage(tempelAnalyticsWidget.messages.unavailable);
            return;
        }

        var value = getMetricValue(response);
        var visitors = parseInt(value, 10);

        if (isNaN(visitors)) {
            showMessage(tempelAnalyticsWidget.messages.unavailable);
            return;
        }

        $visitors.text(visitors.toLocaleString());
    }).fail(function(xhr) {
        if (xhr && (xhr.status === 401 || xhr.status === 403 || xhr.status === 404)) {
            showMessage(tempelAnalyticsWidget.messages.unavailable);
            return;
        }

        showMessage(tempelAnalyticsWidget.messages.error);
    });
});
