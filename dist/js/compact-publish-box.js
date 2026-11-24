(function () {
    'use strict';

    function hideTextBefore(container, marker, fallbackLabel) {
        if (!container || !marker) return;
        Array.prototype.slice.call(container.childNodes).some(function (node) {
            if (node === marker || (node.nodeType === 1 && node.contains(marker))) return true;
            if (node.nodeType !== 3 || !node.nodeValue.trim()) return false;
            var label = node.nodeValue.trim();
            var hidden = document.createElement('span');
            hidden.className = 'screen-reader-text tempel-publish-label';
            hidden.textContent = label || fallbackLabel;
            node.parentNode.replaceChild(hidden, node);
            return false;
        });
    }

    function compactPublishBox() {
        var box = document.getElementById('submitdiv');
        if (!box) return;
        box.classList.add('tempel-compact-publish-box');

        var status = box.querySelector('.misc-pub-post-status');
        hideTextBefore(status, box.querySelector('#post-status-display'), 'Status:');

        var visibility = box.querySelector('.misc-pub-visibility');
        hideTextBefore(visibility, box.querySelector('#post-visibility-display'), 'Zichtbaarheid:');

        var timestamp = box.querySelector('#timestamp');
        hideTextBefore(timestamp, timestamp ? timestamp.querySelector('b') : null, 'Gepubliceerd op:');
    }

    function init() {
        compactPublishBox();
        var box = document.getElementById('submitdiv');
        if (!box) return;
        new MutationObserver(compactPublishBox).observe(box, { childList: true, subtree: true });
    }

    if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', init);
    else init();
}());
