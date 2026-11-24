(function () {
    'use strict';

    if (typeof tempelAI === 'undefined' || typeof wp === 'undefined' || !wp.apiFetch) return;
    wp.apiFetch.use(wp.apiFetch.createNonceMiddleware(tempelAI.nonce));

    var state = { result: '', resultFormat: 'text', sourceHtml: '', sourceFormat: 'text', target: null, lastAcfTarget: null };

    function escapeHtml(value) {
        var div = document.createElement('div');
        div.textContent = value;
        return div.innerHTML;
    }

    function textToHtml(value) {
        return '<p>' + escapeHtml(value).replace(/\n\n+/g, '</p><p>').replace(/\n/g, '<br>') + '</p>';
    }

    function plainText(value) {
        var div = document.createElement('div');
        div.innerHTML = value || '';
        return (div.textContent || '').replace(/\s+/g, ' ').trim();
    }

    function acfFieldLabel(element) {
        var field = element && element.closest ? element.closest('.acf-field') : null;
        var label = field ? field.querySelector(':scope > .acf-label label, :scope > .acf-label') : null;
        var layout = field && field.closest ? field.closest('.layout') : null;
        var layoutTitle = layout ? layout.querySelector(':scope > .acf-fc-layout-handle') : null;
        var parts = [];
        if (layoutTitle) parts.push(layoutTitle.textContent.replace(/\s+/g, ' ').trim());
        if (label) parts.push(label.textContent.replace(/\*/g, '').replace(/\s+/g, ' ').trim());
        return parts.filter(Boolean).join(' — ') || 'ACF-veld';
    }

    function rememberAcfInput(element) {
        if (!element || element.disabled || element.readOnly) return;
        var type = (element.getAttribute('type') || '').toLowerCase();
        if (element.tagName !== 'TEXTAREA' && type !== 'text' && type !== 'search' && type !== 'url' && type !== 'email') return;
        if (!element.closest('.acf-field')) return;
        state.lastAcfTarget = { type: 'acf-input', element: element, label: acfFieldLabel(element) };
    }

    function bindAcfTinyMce(editor) {
        if (!editor || editor.tempelAiBound) return;
        var target = editor.targetElm || document.getElementById(editor.id);
        if (!target || !target.closest) return;
        if (!target.closest('.acf-field')) {
            editor.tempelAiBound = true;
            editor.on('focus', function () { state.lastAcfTarget = null; });
            return;
        }
        editor.tempelAiBound = true;
        editor.on('focus click keyup', function () {
            state.lastAcfTarget = { type: 'acf-wysiwyg', editorId: editor.id, label: acfFieldLabel(target) };
        });
    }

    function bindAcfEditors() {
        document.addEventListener('focusin', function (event) { rememberAcfInput(event.target); });
        document.addEventListener('mousedown', function (event) {
            if (event.target.closest && (event.target.closest('.block-editor') || event.target.id === 'content')) state.lastAcfTarget = null;
            rememberAcfInput(event.target);
        });
        if (typeof tinymce !== 'undefined' && tinymce.editors) tinymce.editors.forEach(bindAcfTinyMce);
        if (typeof acf !== 'undefined' && acf.addAction) {
            acf.addAction('wysiwyg_tinymce_init', function (editor) { bindAcfTinyMce(editor); });
            acf.addAction('append', function () {
                window.setTimeout(function () {
                    if (typeof tinymce !== 'undefined' && tinymce.editors) tinymce.editors.forEach(bindAcfTinyMce);
                }, 100);
            });
        }
    }

    function selectionFromAcf(targetOverride) {
        var target = targetOverride || state.lastAcfTarget;
        if (!target) return null;
        if (target.type === 'acf-input' && document.body.contains(target.element)) {
            var element = target.element;
            var start = typeof element.selectionStart === 'number' ? element.selectionStart : 0;
            var end = typeof element.selectionEnd === 'number' ? element.selectionEnd : 0;
            var hasSelection = end > start;
            return { target: target, text: hasSelection ? element.value.slice(start, end) : element.value, range: hasSelection ? { start: start, end: end } : null, format: 'text', html: '' };
        }
        if (target.type === 'acf-wysiwyg' && typeof tinymce !== 'undefined') {
            var editor = tinymce.get(target.editorId);
            if (!editor) return null;
            var selected = editor.selection.getContent({ format: 'text' }).trim();
            var selectedHtml = editor.selection.getContent({ format: 'html' }).trim();
            return { target: target, text: selected || plainText(editor.getContent()), html: selectedHtml || editor.getContent(), format: 'html', bookmark: selected ? editor.selection.getBookmark(2, true) : null };
        }
        return null;
    }

    function selectionFromGutenberg() {
        if (!wp.data || !wp.data.select('core/block-editor')) return null;
        var editor = wp.data.select('core/block-editor');
        var id = editor.getSelectedBlockClientId();
        var block = id ? editor.getBlock(id) : null;
        if (!block) return null;
        var value = block.attributes && (block.attributes.content || block.attributes.value || block.attributes.caption);
        if (typeof value !== 'string') return null;
        return { target: { type: 'gutenberg', blockId: id, label: 'Geselecteerd WordPress-blok' }, text: plainText(value), html: value, format: 'html' };
    }

    function selectionFromClassic() {
        if (typeof tinymce === 'undefined' || !tinymce.activeEditor) return null;
        var editor = tinymce.activeEditor;
        var target = editor.targetElm || document.getElementById(editor.id);
        if (target && target.closest && target.closest('.acf-field')) return null;
        var selected = editor.selection.getContent({ format: 'text' }).trim();
        var selectedHtml = editor.selection.getContent({ format: 'html' }).trim();
        return { target: { type: 'classic', editorId: editor.id, label: 'Klassieke editor' }, text: selected || plainText(editor.getContent()), html: selectedHtml || editor.getContent(), format: 'html', bookmark: selected ? editor.selection.getBookmark(2, true) : null };
    }

    function useSelection(selection) {
        state.target = selection.target;
        state.target.range = selection.range || null;
        state.target.bookmark = selection.bookmark || null;
        state.sourceFormat = selection.format || 'text';
        state.sourceHtml = selection.html || '';
        document.getElementById('tempel-ai-source').value = selection.text || '';
        document.getElementById('tempel-ai-result').innerHTML = '';
        document.getElementById('tempel-ai-apply').disabled = true;
        updateTargetLabel();
        setStatus('Tekst uit “' + state.target.label + '” is overgenomen.', 'success');
    }

    function readSelection() {
        var selection = selectionFromAcf() || selectionFromClassic() || selectionFromGutenberg();
        if (!selection) {
            state.target = null;
            updateTargetLabel();
            setStatus('Klik eerst in een tekstveld of selecteer een tekstblok.', 'error');
            return;
        }
        useSelection(selection);
    }

    function updateTargetLabel() {
        var element = document.getElementById('tempel-ai-target');
        if (!element) return;
        element.textContent = state.target ? state.target.label : 'Nog geen veld gekozen';
        element.classList.toggle('is-selected', !!state.target);
    }

    function setStatus(message, type) {
        var status = document.getElementById('tempel-ai-status');
        status.textContent = message || '';
        status.className = 'tempel-ai-status' + (type ? ' is-' + type : '');
    }

    function openPanel(selection) {
        document.getElementById('tempel-ai-panel').hidden = false;
        document.getElementById('tempel-ai-launcher').setAttribute('aria-expanded', 'true');
        if (selection) useSelection(selection);
        else readSelection();
        document.getElementById('tempel-ai-instruction').focus();
    }

    function targetFromAcfField(field) {
        if (!field) return null;
        if (field.classList.contains('acf-field-wysiwyg')) {
            var editorElement = field.querySelector(':scope > .acf-input textarea.wp-editor-area');
            if (!editorElement) return null;
            if (typeof tinymce !== 'undefined' && tinymce.get(editorElement.id)) return { type: 'acf-wysiwyg', editorId: editorElement.id, label: acfFieldLabel(editorElement) };
            return { type: 'acf-input', element: editorElement, label: acfFieldLabel(editorElement) };
        }
        var element = field.querySelector(':scope > .acf-input textarea, :scope > .acf-input input[type="text"], :scope > .acf-input input[type="search"], :scope > .acf-input input[type="url"], :scope > .acf-input input[type="email"]');
        return element ? { type: 'acf-input', element: element, label: acfFieldLabel(element) } : null;
    }

    function enhanceAcfFields(root) {
        var scope = root && root.querySelectorAll ? root : document;
        var selector = '.acf-field-text, .acf-field-textarea, .acf-field-wysiwyg, .acf-field-url, .acf-field-email';
        var fields = [];
        if (scope.matches && scope.matches(selector)) fields.push(scope);
        fields = fields.concat(Array.prototype.slice.call(scope.querySelectorAll(selector)));
        fields.forEach(function (field) {
            if (field.querySelector(':scope > .acf-label .tempel-ai-inline-button')) return;
            var label = field.querySelector(':scope > .acf-label');
            if (!label || !targetFromAcfField(field)) return;
            var button = document.createElement('button');
            button.type = 'button';
            button.className = 'tempel-ai-inline-button';
            button.setAttribute('aria-label', 'AI gebruiken voor ' + acfFieldLabel(field));
            button.innerHTML = '<span aria-hidden="true">✦</span> AI';
            button.addEventListener('mousedown', function (event) {
                event.preventDefault();
                event.stopPropagation();
                var target = targetFromAcfField(field);
                if (!target) return;
                state.lastAcfTarget = target;
                var selection = selectionFromAcf(target);
                if (selection) openPanel(selection);
            });
            label.insertBefore(button, label.firstChild);
        });
        var launcher = document.getElementById('tempel-ai-launcher');
        if (launcher && document.querySelector('.tempel-ai-inline-button')) launcher.hidden = true;
    }

    function addClassicEditorButton() {
        var tools = document.getElementById('wp-content-media-buttons');
        if (!tools || document.getElementById('tempel-ai-classic-button')) return;
        var button = document.createElement('button');
        button.type = 'button';
        button.id = 'tempel-ai-classic-button';
        button.className = 'button tempel-ai-classic-button';
        button.innerHTML = '<span aria-hidden="true">✦</span> AI schrijven';
        button.addEventListener('mousedown', function (event) {
            event.preventDefault();
            state.lastAcfTarget = null;
            var selection = selectionFromClassic();
            if (selection) openPanel(selection);
        });
        tools.appendChild(button);
        var launcher = document.getElementById('tempel-ai-launcher');
        if (launcher) launcher.hidden = true;
    }

    function closePanel() {
        document.getElementById('tempel-ai-panel').hidden = true;
        document.getElementById('tempel-ai-launcher').setAttribute('aria-expanded', 'false');
        document.getElementById('tempel-ai-launcher').focus();
    }

    function generate() {
        var button = document.getElementById('tempel-ai-generate');
        var text = document.getElementById('tempel-ai-source').value.trim();
        var instruction = document.getElementById('tempel-ai-instruction').value.trim();
        var action = document.getElementById('tempel-ai-action').value;
        if (!text && !instruction) return setStatus('Voeg tekst of een opdracht toe.', 'error');
        if ((text + instruction).length > tempelAI.maxInputChars) return setStatus('De invoer is te lang.', 'error');
        button.disabled = true;
        setStatus('Tekst wordt gemaakt…', 'loading');
        wp.apiFetch({ path: tempelAI.path, method: 'POST', data: { post_id: tempelAI.postId, action: action, text: text, instruction: instruction, format: state.sourceFormat, html: state.sourceFormat === 'html' ? state.sourceHtml : '' } })
            .then(function (response) {
                state.result = response.text || '';
                state.resultFormat = response.format === 'html' ? 'html' : 'text';
                var preview = document.getElementById('tempel-ai-result');
                if (state.resultFormat === 'html') preview.innerHTML = state.result;
                else preview.textContent = state.result;
                document.getElementById('tempel-ai-apply').disabled = !state.result;
                setStatus('Voorbeeld klaar. Controleer de tekst vóór je hem invoegt.', 'success');
            })
            .catch(function (error) { setStatus(error && error.message ? error.message : 'De opdracht is mislukt.', 'error'); })
            .finally(function () { button.disabled = false; });
    }

    function notifyAcfChange(element) {
        element.dispatchEvent(new Event('input', { bubbles: true }));
        element.dispatchEvent(new Event('change', { bubbles: true }));
        if (typeof jQuery !== 'undefined') jQuery(element).trigger('change');
    }

    function applyToAcfInput(target, value) {
        var element = target.element;
        if (!document.body.contains(element)) return false;
        if (target.range) element.value = element.value.slice(0, target.range.start) + value + element.value.slice(target.range.end);
        else element.value = value;
        notifyAcfChange(element);
        return true;
    }

    function applyToTinyMce(target, value, format) {
        if (typeof tinymce === 'undefined') return false;
        var editor = tinymce.get(target.editorId);
        if (!editor) return false;
        if (target.bookmark) {
            editor.selection.moveToBookmark(target.bookmark);
            editor.selection.setContent(format === 'html' ? value : escapeHtml(value).replace(/\n/g, '<br>'));
        } else editor.setContent(format === 'html' ? value : textToHtml(value));
        editor.save();
        if (editor.targetElm) notifyAcfChange(editor.targetElm);
        editor.fire('change');
        return true;
    }

    function applyResult() {
        var preview = document.getElementById('tempel-ai-result');
        var value = state.resultFormat === 'html' ? preview.innerHTML.trim() : preview.innerText.trim();
        var target = state.target;
        if (!value) return;
        if (target && target.type === 'acf-input' && applyToAcfInput(target, value)) return setStatus('Het ACF-veld is bijgewerkt.', 'success');
        if (target && target.type === 'acf-wysiwyg' && applyToTinyMce(target, value, state.resultFormat)) return setStatus('De ACF-editor is bijgewerkt.', 'success');
        if (target && target.type === 'gutenberg' && wp.data && wp.data.dispatch('core/block-editor')) {
            var editor = wp.data.select('core/block-editor');
            var block = editor.getBlock(target.blockId);
            var attribute = block && Object.prototype.hasOwnProperty.call(block.attributes, 'content') ? 'content' : block && Object.prototype.hasOwnProperty.call(block.attributes, 'value') ? 'value' : '';
            if (attribute) {
                var update = {};
                update[attribute] = state.resultFormat === 'html' ? value : escapeHtml(value).replace(/\n/g, '<br>');
                wp.data.dispatch('core/block-editor').updateBlockAttributes(target.blockId, update);
                return setStatus('Het geselecteerde blok is vervangen.', 'success');
            }
        }
        if (target && target.type === 'classic' && applyToTinyMce(target, value, state.resultFormat)) return setStatus('De klassieke editor is bijgewerkt.', 'success');
        setStatus('Het oorspronkelijke veld bestaat niet meer. Kies het veld opnieuw.', 'error');
    }

    function build() {
        var actions = Object.keys(tempelAI.actions).map(function (key) { return '<option value="' + key + '">' + escapeHtml(tempelAI.actions[key]) + '</option>'; }).join('');
        var launcher = document.createElement('button');
        launcher.type = 'button';
        launcher.id = 'tempel-ai-launcher';
        launcher.className = 'tempel-ai-launcher';
        launcher.setAttribute('aria-expanded', 'false');
        launcher.setAttribute('aria-controls', 'tempel-ai-panel');
        launcher.textContent = 'AI schrijven';
        var panel = document.createElement('section');
        panel.id = 'tempel-ai-panel';
        panel.className = 'tempel-ai-panel';
        panel.hidden = true;
        panel.setAttribute('aria-label', 'Tempel AI');
        panel.innerHTML = '<header><h2>Tempel AI</h2><button type="button" id="tempel-ai-close" aria-label="Sluiten">×</button></header>' +
            '<p id="tempel-ai-target" class="tempel-ai-target">Nog geen veld gekozen</p>' +
            '<div class="tempel-ai-field"><label for="tempel-ai-action">Actie</label><select id="tempel-ai-action">' + actions + '</select></div>' +
            '<div class="tempel-ai-field"><label for="tempel-ai-instruction">Opdracht</label><textarea id="tempel-ai-instruction" rows="2" placeholder="Bijvoorbeeld: vriendelijk, duidelijk en op B1-niveau"></textarea></div>' +
            '<div class="tempel-ai-field"><div class="tempel-ai-label-row"><label for="tempel-ai-source">Brontekst</label><button type="button" id="tempel-ai-selection">Neem actief veld opnieuw over</button></div><textarea id="tempel-ai-source" rows="5"></textarea></div>' +
            '<button type="button" class="button button-primary" id="tempel-ai-generate">Maak voorbeeld</button><p id="tempel-ai-status" class="tempel-ai-status" aria-live="polite"></p>' +
            '<div class="tempel-ai-field"><label for="tempel-ai-result">Voorbeeld</label><div id="tempel-ai-result" class="tempel-ai-result" contenteditable="true" role="textbox" aria-multiline="true"></div></div><button type="button" class="button" id="tempel-ai-apply" disabled>Terugplaatsen in gekozen veld</button>';
        document.body.appendChild(launcher);
        document.body.appendChild(panel);
        bindAcfEditors();
        enhanceAcfFields(document);
        addClassicEditorButton();
        if (typeof acf !== 'undefined' && acf.addAction) {
            acf.addAction('append', function (element) { enhanceAcfFields(element && element[0] ? element[0] : element); });
        }
        new MutationObserver(function (mutations) {
            mutations.forEach(function (mutation) {
                Array.prototype.forEach.call(mutation.addedNodes, function (node) {
                    if (node.nodeType === 1) enhanceAcfFields(node);
                });
            });
        }).observe(document.body, { childList: true, subtree: true });
        launcher.addEventListener('click', function () { panel.hidden ? openPanel() : closePanel(); });
        document.getElementById('tempel-ai-close').addEventListener('click', closePanel);
        document.getElementById('tempel-ai-selection').addEventListener('click', readSelection);
        document.getElementById('tempel-ai-source').addEventListener('input', function () {
            state.sourceFormat = 'text';
            state.sourceHtml = '';
        });
        document.getElementById('tempel-ai-result').addEventListener('click', function (event) {
            if (event.target.closest && event.target.closest('a')) event.preventDefault();
        });
        document.getElementById('tempel-ai-generate').addEventListener('click', generate);
        document.getElementById('tempel-ai-apply').addEventListener('click', applyResult);
        document.addEventListener('keydown', function (event) { if (event.key === 'Escape' && !panel.hidden) closePanel(); });
    }

    if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', build);
    else build();
}());
