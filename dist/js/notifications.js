(function () {
    'use strict';

    function cookieName(key) {
        return 'tempel_notification_' + key;
    }

    function hasCookie(name) {
        return document.cookie.split(';').some(function (cookie) {
            return cookie.trim().indexOf(name + '=') === 0;
        });
    }

    function setCookie(name, days) {
        var expires = new Date(Date.now() + days * 86400000).toUTCString();
        document.cookie = name + '=1; expires=' + expires + '; path=/; SameSite=Lax' + (location.protocol === 'https:' ? '; Secure' : '');
    }

    function isEligible(notification) {
        if (notification.dataset.mobile === '0' && window.matchMedia('(max-width: 767px)').matches) {
            return false;
        }

        return notification.dataset.frequency === 'always' || !hasCookie(cookieName(notification.dataset.cookieKey));
    }

    function getPosition(notification) {
        if (notification.classList.contains('tempel-notification--top')) {
            return 'top';
        }
        if (notification.classList.contains('tempel-notification--bottom')) {
            return 'bottom';
        }
        return 'center';
    }

    function sortQueue(queue) {
        queue.sort(function (first, second) {
            var dismissibleDifference = parseInt(second.dataset.dismissible, 10) - parseInt(first.dataset.dismissible, 10);
            if (dismissibleDifference !== 0) {
                return dismissibleDifference;
            }

            if (first.dataset.scope !== second.dataset.scope) {
                return first.dataset.scope === 'specific' ? -1 : 1;
            }

            return 0;
        });
    }

    function initQueue(queue) {
        function showAt(index) {
            var notification = queue[index];

            if (!notification) {
                return;
            }

            var previousFocus = document.activeElement;
            var closeButton = notification.querySelector('[data-tempel-notification-close]');
            var actionButton = notification.querySelector('.tempel-notification__button');
            var backdrop = notification.previousElementSibling && notification.previousElementSibling.hasAttribute('data-tempel-notification-backdrop')
                ? notification.previousElementSibling
                : null;

            if (backdrop) {
                document.body.appendChild(backdrop);
            }
            document.body.appendChild(notification);

            function remember() {
                if (notification.dataset.frequency !== 'always') {
                    setCookie(cookieName(notification.dataset.cookieKey), parseInt(notification.dataset.cookieDays, 10) || 30);
                }
            }

            function hide() {
                notification.hidden = true;
                if (notification.classList.contains('tempel-notification--center')) {
                    document.body.classList.remove('tempel-notification-open');
                }
                if (backdrop) {
                    backdrop.hidden = true;
                }
                remember();
            }

            function close() {
                hide();
                if (previousFocus && typeof previousFocus.focus === 'function') {
                    previousFocus.focus();
                }
                showAt(index + 1);
            }

            function followAction() {
                hide();
            }

            function show() {
                notification.hidden = false;
                if (notification.classList.contains('tempel-notification--center')) {
                    document.body.classList.add('tempel-notification-open');
                }
                if (backdrop) {
                    backdrop.hidden = false;
                }
                if (!closeButton) {
                    remember();
                }
                if (closeButton && notification.classList.contains('tempel-notification--center')) {
                    closeButton.focus();
                }
            }

            if (closeButton) {
                closeButton.addEventListener('click', close);
            }
            if (actionButton) {
                actionButton.addEventListener('click', followAction);
            }
            if (backdrop && closeButton) {
                backdrop.addEventListener('click', close);
            }
            document.addEventListener('keydown', function (event) {
                if (event.key === 'Escape' && !notification.hidden && closeButton) {
                    close();
                }
            });

            window.setTimeout(show, (parseInt(notification.dataset.delay, 10) || 0) * 1000);
        }

        showAt(0);
    }

    function init() {
        var queues = { center: [], top: [], bottom: [] };

        Array.prototype.slice.call(document.querySelectorAll('[data-tempel-notification]'))
            .filter(isEligible)
            .forEach(function (notification) {
                queues[getPosition(notification)].push(notification);
            });

        sortQueue(queues.top);
        sortQueue(queues.center);
        sortQueue(queues.bottom);

        initQueue(queues.top);
        initQueue(queues.center);
        initQueue(queues.bottom);
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
}());
