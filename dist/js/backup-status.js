(function () {
    'use strict';

    var config = window.tempelBackupStatus || {};
    var page = document.querySelector('[data-tempel-backup-page]');
    if (!page || !window.fetch) return;

    var stateNode = page.querySelector('[data-tempel-backup-state]');
    var timeNode = page.querySelector('[data-tempel-backup-time]');
    var card = page.querySelector('[data-tempel-backup-result-card]');
    var messageWrap = page.querySelector('[data-tempel-backup-message-wrap]');
    var messageNode = page.querySelector('[data-tempel-backup-message]');
    var fileNode = page.querySelector('[data-tempel-backup-file]');
    var progressWrap = page.querySelector('[data-tempel-backup-progress]');
    var phaseNode = page.querySelector('[data-tempel-backup-phase]');
    var percentNode = page.querySelector('[data-tempel-backup-percent]');
    var progressMessage = page.querySelector('[data-tempel-backup-progress-message]');
    var progress = progressWrap ? progressWrap.querySelector('progress') : null;
    var labels = config.labels || {};
    var timer = null;
    var failures = 0;

    function stateClass(state) {
        if (state === 'success' || state === 'connected') return 'success';
        if (state === 'error' || state === 'disconnected') return 'error';
        return 'neutral';
    }

    function render(data) {
        var label = labels[data.state] || labels.idle || data.state;
        var className = stateClass(data.state);
        if (stateNode) stateNode.textContent = label;
        if (timeNode && data.formattedTime) timeNode.textContent = data.formattedTime;
        if (card) card.className = 'tempel-backup-card tempel-backup-card--' + className;
        if (messageWrap) {
            messageWrap.hidden = !data.message;
            messageWrap.className = 'tempel-backup-last-message tempel-backup-last-message--' + className;
        }
        if (messageNode) messageNode.textContent = data.message || '';
        if (fileNode) { fileNode.textContent = data.file || ''; fileNode.hidden = !data.file; }
        if (progressWrap) progressWrap.hidden = !data.active;
        if (phaseNode) phaseNode.textContent = label;
        if (percentNode) percentNode.textContent = Math.max(0, Math.min(100, Number(data.progress) || 0)) + '%';
        if (progress) {
            if (data.state === 'uploading') progress.value = Math.max(0, Math.min(100, Number(data.progress) || 0));
            else progress.removeAttribute('value');
        }
        if (progressMessage) progressMessage.textContent = data.message || '';
        if (!data.active && timer) { window.clearTimeout(timer); timer = null; }
    }

    function poll() {
        var body = new URLSearchParams({action: 'tempel_dropbox_backup_status', nonce: config.nonce || ''});
        window.fetch(config.ajaxUrl, {method: 'POST', credentials: 'same-origin', headers: {'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8'}, body: body.toString()})
            .then(function (response) { if (!response.ok) throw new Error('HTTP ' + response.status); return response.json(); })
            .then(function (response) {
                if (!response.success) throw new Error('Status error');
                failures = 0;
                render(response.data);
                if (response.data.active) timer = window.setTimeout(poll, Number(config.interval) || 2500);
            })
            .catch(function () {
                failures += 1;
                if (progressMessage && failures > 1) progressMessage.textContent = labels.unavailable || 'Status tijdelijk niet beschikbaar.';
                if (failures < 5) timer = window.setTimeout(poll, Math.min(15000, (Number(config.interval) || 2500) * failures));
            });
    }

    poll();
}());
