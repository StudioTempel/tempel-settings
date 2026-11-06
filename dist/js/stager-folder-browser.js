(function () {
    'use strict';
    const panel = document.getElementById('stager-folders');
    if (!panel) return;
    const browse = document.getElementById('stager-browse');
    const status = document.getElementById('stager-folder-status');
    const pathLabel = document.getElementById('stager-folder-path');
    const list = document.getElementById('stager-folder-list');
    const up = document.getElementById('stager-folder-up');
    const home = document.getElementById('stager-folder-home');
    const select = document.getElementById('stager-folder-select');
    const close = document.getElementById('stager-folder-close');
    const root = document.getElementById('stager-root');
    const fields = ['host', 'port', 'user', 'pass'].map(name => document.getElementById('stager-' + name));
    let current = null;
    let generation = 0;
    let controller = null;

    function invalidate() {
        generation++;
        if (controller) controller.abort();
        current = null;
        select.disabled = true;
        up.disabled = true;
        browse.disabled = false;
        home.disabled = false;
        list.replaceChildren();
        pathLabel.textContent = '';
        panel.setAttribute('aria-busy', 'false');
    }
    fields.forEach(field => field.addEventListener('input', () => {
        invalidate();
        status.textContent = 'Verbindingsgegevens gewijzigd. Klik opnieuw op Bladeren op server.';
    }));

    async function load(path) {
        if (fields.some(field => !field.reportValidity())) return;
        invalidate();
        const requestGeneration = generation;
        controller = new AbortController();
        const requestController = controller;
        const timer = setTimeout(() => requestController.abort(), 90000);
        browse.disabled = true;
        home.disabled = true;
        panel.setAttribute('aria-busy', 'true');
        status.textContent = 'Verbinden en mappen ophalen…';
        const body = new URLSearchParams({action: 'tempel_stager_browse', nonce: panel.dataset.nonce, path});
        fields.forEach(field => body.set(field.name, field.value));
        try {
            const response = await fetch(panel.dataset.endpoint, {method: 'POST', credentials: 'same-origin', body, signal: controller.signal});
            const payload = await response.json();
            if (requestGeneration !== generation) return;
            if (!response.ok || !payload.success) throw new Error(payload.data?.message || 'Mappen ophalen mislukt. Vernieuw de pagina en probeer opnieuw.');
            current = payload.data;
            pathLabel.textContent = current.path;
            status.textContent = current.wordpress ? 'WordPress gevonden. Je kunt deze map als doel kiezen.' : 'Nog geen WordPress in deze map. Open een submap of ga een map omhoog.';
            current.directories.forEach(directory => {
                const item = document.createElement('li');
                const button = document.createElement('button');
                button.type = 'button';
                button.className = 'button';
                button.style.marginTop = '0';
                button.textContent = '📁 ' + directory.name;
                button.addEventListener('click', () => load(directory.path));
                item.append(button);
                list.append(item);
            });
            if (!current.directories.length) {
                const item = document.createElement('li');
                item.textContent = 'Geen submappen.';
                list.append(item);
            }
            up.disabled = !current.parent;
            select.disabled = !current.wordpress;
        } catch (error) {
            if (requestGeneration !== generation) return;
            status.textContent = error.name === 'AbortError' ? 'De verbinding duurde te lang. Probeer opnieuw.' : error.message;
        } finally {
            clearTimeout(timer);
            if (requestGeneration === generation) {
                browse.disabled = false;
                home.disabled = false;
                panel.setAttribute('aria-busy', 'false');
            }
        }
    }
    browse.addEventListener('click', () => {
        if (fields.some(field => !field.reportValidity())) return;
        if (typeof panel.showModal === 'function' && !panel.open) panel.showModal();
        load(root.value.trim());
    });
    close.addEventListener('click', () => panel.close());
    panel.addEventListener('click', event => {
        if (event.target === panel) panel.close();
    });
    home.addEventListener('click', () => load(''));
    up.addEventListener('click', () => { if (current?.parent) load(current.parent); });
    select.addEventListener('click', () => {
        if (!current?.wordpress) return;
        root.value = current.path;
        panel.close();
        root.focus();
    });

    const dropboxDialog = document.getElementById('stager-dropbox-dialog');
    if (dropboxDialog) {
        const dropboxBrowse = document.getElementById('stager-dropbox-browse');
        const dropboxClose = document.getElementById('stager-dropbox-close');
        const dropboxStatus = document.getElementById('stager-dropbox-status');
        const dropboxList = document.getElementById('stager-dropbox-list');
        const dropboxPath = document.getElementById('stager-dropbox-path');
        const dropboxSelected = document.getElementById('stager-dropbox-selected');
        const packageInput = document.getElementById('stager-package');

        dropboxBrowse.addEventListener('click', async () => {
            dropboxDialog.showModal();
            dropboxList.replaceChildren();
            dropboxStatus.textContent = 'Back-ups ophalen uit Dropbox…';
            const body = new URLSearchParams({action: 'tempel_stager_dropbox_packages', nonce: dropboxDialog.dataset.nonce});
            try {
                const response = await fetch(dropboxDialog.dataset.endpoint, {method: 'POST', credentials: 'same-origin', body});
                const payload = await response.json();
                if (!response.ok || !payload.success) throw new Error(payload.data?.message || 'Dropbox-back-ups ophalen mislukt.');
                const packages = payload.data.packages || [];
                dropboxStatus.textContent = packages.length ? 'Kies een pakket. Nieuwe Tempel-sitepakketten kunnen direct worden verhuisd.' : 'Geen zipbestanden gevonden in de ingestelde Dropbox-back-upmap.';
                packages.forEach(item => {
                    const row = document.createElement('li');
                    const button = document.createElement('button');
                    button.type = 'button';
                    button.className = 'button';
                    const size = item.size >= 1073741824 ? (item.size / 1073741824).toFixed(1) + ' GB' : (item.size / 1048576).toFixed(0) + ' MB';
                    button.textContent = item.name + ' · ' + size;
                    button.addEventListener('click', () => {
                        dropboxPath.value = item.path;
                        packageInput.value = '';
                        dropboxSelected.textContent = 'Gekozen uit Dropbox: ' + item.name;
                        dropboxDialog.close();
                    });
                    row.append(button);
                    dropboxList.append(row);
                });
            } catch (error) {
                dropboxStatus.textContent = error.message;
            }
        });
        dropboxClose.addEventListener('click', () => dropboxDialog.close());
        packageInput.addEventListener('change', () => {
            if (packageInput.files.length) {
                dropboxPath.value = '';
                dropboxSelected.textContent = '';
            }
        });
    }
})();
