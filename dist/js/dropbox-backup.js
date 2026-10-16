(function () {
    'use strict';

    var config = window.tempelDropboxBrowser || {};
    var openButton = document.getElementById('tempel-dropbox-browse');
    var browser = document.getElementById('tempel-dropbox-browser');
    var input = document.getElementById('folder');
    var folders = document.getElementById('tempel-dropbox-folders');
    var pathLabel = document.getElementById('tempel-dropbox-current-path');
    var message = document.getElementById('tempel-dropbox-browser-message');
    var upButton = document.getElementById('tempel-dropbox-up');
    var selectButton = document.getElementById('tempel-dropbox-select');
    var currentPath = '/';
    var parentPath = '/';

    if (!openButton || !browser || !input || !folders) {
        return;
    }

    function setMessage(text, isError) {
        message.textContent = text || '';
        message.classList.toggle('is-error', Boolean(isError));
    }

    function folderButton(folder) {
        var button = document.createElement('button');
        var icon = document.createElement('span');
        var label = document.createElement('span');

        button.type = 'button';
        button.className = 'tempel-dropbox-folder';
        icon.className = 'tempel-dropbox-folder__icon';
        icon.setAttribute('aria-hidden', 'true');
        label.textContent = folder.name;
        button.appendChild(icon);
        button.appendChild(label);
        button.addEventListener('click', function () {
            loadFolders(folder.path);
        });
        return button;
    }

    function loadFolders(path) {
        var data = new URLSearchParams();
        data.append('action', 'tempel_dropbox_list_folders');
        data.append('nonce', config.nonce || '');
        data.append('path', path || '/');

        folders.innerHTML = '';
        setMessage((config.labels && config.labels.loading) || 'Mappen laden…', false);
        upButton.disabled = true;

        window.fetch(config.ajaxUrl, {
            method: 'POST',
            credentials: 'same-origin',
            headers: {'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8'},
            body: data.toString()
        }).then(function (response) {
            return response.json();
        }).then(function (response) {
            if (!response.success) {
                throw new Error(response.data && response.data.message ? response.data.message : 'Dropbox error');
            }

            currentPath = response.data.path || '/';
            parentPath = response.data.parent || '/';
            pathLabel.textContent = currentPath;
            upButton.disabled = currentPath === '/';
            folders.innerHTML = '';

            if (!response.data.folders || !response.data.folders.length) {
                setMessage((config.labels && config.labels.empty) || 'Deze map bevat geen andere mappen.', false);
                return;
            }

            setMessage('', false);
            response.data.folders.forEach(function (folder) {
                folders.appendChild(folderButton(folder));
            });
        }).catch(function (error) {
            setMessage(error.message || ((config.labels && config.labels.error) || 'De Dropbox-mappen konden niet worden geladen.'), true);
        });
    }

    openButton.addEventListener('click', function () {
        browser.hidden = !browser.hidden;
        openButton.setAttribute('aria-expanded', browser.hidden ? 'false' : 'true');
        if (!browser.hidden) {
            loadFolders('/');
        }
    });

    upButton.addEventListener('click', function () {
        loadFolders(parentPath);
    });

    selectButton.addEventListener('click', function () {
        input.value = currentPath;
        browser.hidden = true;
        openButton.setAttribute('aria-expanded', 'false');
        input.focus();
    });
}());
