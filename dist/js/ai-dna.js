(function () {
    'use strict';

    function status(message, type) {
        var element = document.getElementById('tempel-dna-status');
        if (!element) return;
        element.textContent = message || '';
        element.className = 'tempel-ui-field__help' + (type ? ' is-' + type : '');
    }

    function currentDna() {
        var values = {};
        Object.keys(tempelDNA.fields).forEach(function (key) {
            var field = document.getElementById('tempel_dna_' + key);
            values[key] = field ? field.value : '';
            var tags = document.getElementById('tempel_dna_' + key + '_tags');
            values[key + '_tags'] = tags ? tags.value : '';
        });
        return values;
    }

    function proposalDna() {
        var values = {};
        Object.keys(tempelDNA.fields).forEach(function (key) {
            [key, key + '_tags'].forEach(function (fieldKey) {
                var field = document.getElementById('tempel_dna_proposal_' + fieldKey);
                values[fieldKey] = field ? field.value : '';
            });
        });
        return values;
    }

    function setDnaValues(values, focusKey, mode) {
        Object.keys(tempelDNA.fields).forEach(function (key) {
            if (focusKey && key !== focusKey) return;
            var field = document.getElementById('tempel_dna_' + key);
            var tags = document.getElementById('tempel_dna_' + key + '_tags');
            if (field && typeof values[key] === 'string' && values[key].trim()) {
                if (mode === 'empty' && field.value.trim()) return;
                field.value = mode === 'append' && field.value.trim() ? field.value.trim() + '\n\n' + values[key].trim() : values[key];
                field.dispatchEvent(new Event('change', { bubbles: true }));
                autoSize(field);
                var reviewed = document.querySelector('[name="tempel_ai_dna[' + key + '_reviewed]"]');
                if (reviewed) {
                    reviewed.checked = false;
                    reviewed.dispatchEvent(new Event('change', { bubbles: true }));
                }
            }
            if (tags && typeof values[key + '_tags'] === 'string' && values[key + '_tags'].trim()) {
                tags.value = mode === 'append' ? parseTags(tags.value + ', ' + values[key + '_tags']).join(', ') : values[key + '_tags'];
                tags.dispatchEvent(new Event('change', { bubbles: true }));
            }
        });
        updateGroupStatuses();
    }

    function updateGroupStatuses() {
        document.querySelectorAll('.tempel-dna-group[data-dna-group]').forEach(function (group) {
            var fields = group.querySelectorAll('.tempel-ai-dna-field[data-dna-field]');
            var filled = 0;
            var reviewed = 0;
            fields.forEach(function (row) {
                var key = row.getAttribute('data-dna-field');
                var value = document.getElementById('tempel_dna_' + key);
                var check = row.querySelector('[name="tempel_ai_dna[' + key + '_reviewed]"]');
                if (value && value.value.trim()) {
                    filled += 1;
                    if (check && check.checked) reviewed += 1;
                }
            });
            var filledLabel = group.querySelector('[data-dna-filled]');
            var statusLabel = group.querySelector('[data-dna-review-status]');
            if (filledLabel) filledLabel.textContent = filled + ' van ' + fields.length + ' ingevuld';
            if (statusLabel) statusLabel.textContent = reviewed + ' gecontroleerd · ' + Math.max(0, filled - reviewed) + ' AI';
        });
    }

    function storeProposal(values, focusKey) {
        Object.keys(tempelDNA.fields).forEach(function (key) {
            if (focusKey && key !== focusKey) return;
            [key, key + '_tags'].forEach(function (fieldKey) {
                var field = document.getElementById('tempel_dna_proposal_' + fieldKey);
                if (!field || typeof values[fieldKey] !== 'string') return;
                field.value = values[fieldKey];
                field.dispatchEvent(new Event('change', { bubbles: true }));
            });
        });
        renderProposal();
    }

    function clearProposal(key) {
        var keys = key ? [key, key + '_tags'] : Object.keys(tempelDNA.fields).reduce(function (all, fieldKey) {
            return all.concat([fieldKey, fieldKey + '_tags']);
        }, []);
        keys.forEach(function (fieldKey) {
            var field = document.getElementById('tempel_dna_proposal_' + fieldKey);
            if (!field) return;
            field.value = '';
            field.dispatchEvent(new Event('change', { bubbles: true }));
        });
        renderProposal();
    }

    function autoSize(field) {
        if (!field || field.tagName !== 'TEXTAREA') return;
        field.style.height = 'auto';
        field.style.height = Math.max(field.scrollHeight + 2, 96) + 'px';
    }

    function progress(job, startedAt) {
        var container = document.getElementById('tempel-dna-progress');
        if (!container) return;
        container.hidden = false;
        var bar = container.querySelector('progress');
        var phase = container.querySelector('.tempel-ui-progress__phase');
        var percent = container.querySelector('.tempel-ui-progress__percent');
        var elapsed = Math.max(0, Math.round((Date.now() - startedAt) / 1000));
        if (phase) phase.textContent = job.phase || 'Analyse uitvoeren';
        if (job.indeterminate) {
            bar.removeAttribute('value');
            if (percent) percent.textContent = elapsed + ' sec.';
        } else {
            var value = Math.max(0, Math.min(100, Number(job.progress || 0)));
            bar.value = value;
            if (percent) percent.textContent = value + '% · ' + elapsed + ' sec.';
        }
        updateActivity(job, elapsed);
    }

    function updateActivity(job, elapsed) {
        var items = document.querySelectorAll('#tempel-dna-progress .tempel-dna-activity li');
        if (!items.length) return;
        var active = Math.min(items.length - 1, Math.floor(elapsed / 7));
        if (Number(job.progress || 0) >= 15) active = Math.max(active, 1);
        if (job.indeterminate) active = Math.max(active, 4);
        if (job.status === 'complete') active = items.length;

        items.forEach(function (item, index) {
            item.classList.toggle('is-complete', index < active || job.status === 'complete');
            item.classList.toggle('is-active', index === active && job.status !== 'complete');
        });
    }

    function finishProgress() {
        var container = document.getElementById('tempel-dna-progress');
        if (container) container.hidden = true;
    }

    function poll(jobId, startedAt, button, focusKey) {
        wp.apiFetch({ path: tempelDNA.statusPath + encodeURIComponent(jobId), method: 'GET', headers: { 'X-WP-Nonce': tempelDNA.nonce } })
            .then(function (job) {
                progress(job, startedAt);
                if (job.status === 'complete') {
                    storeProposal((job.result && job.result.dna) || {}, focusKey);
                    showSources((job.result && job.result.sources) || []);
                    finishProgress();
                    status('Onderzoek afgerond. Het voorstel staat apart klaar; het huidige DNA is nog niet gewijzigd.', 'success');
                    button.disabled = false;
                    return;
                }
                if (job.status === 'failed') throw new Error(job.error || 'Het DNA-onderzoek is mislukt.');
                window.setTimeout(function () { poll(jobId, startedAt, button, focusKey); }, 1800);
            })
            .catch(function (error) {
                finishProgress();
                status(error && error.message ? error.message : 'Het DNA-voorstel kon niet worden gemaakt.', 'error');
                button.disabled = false;
            });
    }

    function showSources(sources) {
        var container = document.getElementById('tempel-dna-sources');
        if (!container) return;
        container.innerHTML = '';
        if (!Array.isArray(sources) || !sources.length) {
            container.hidden = true;
            return;
        }
        var header = document.createElement('div');
        header.className = 'tempel-dna-sources__header';
        var headingWrap = document.createElement('div');
        var title = document.createElement('strong');
        title.textContent = 'Gebruikte bronnen en input';
        var intro = document.createElement('span');
        intro.textContent = 'Transparant overzicht van de input achter dit DNA-voorstel.';
        headingWrap.appendChild(title);
        headingWrap.appendChild(intro);
        var count = document.createElement('span');
        count.className = 'tempel-dna-sources__count';
        count.textContent = sources.length + (sources.length === 1 ? ' bron' : ' bronnen');
        header.appendChild(headingWrap);
        header.appendChild(count);
        var grid = document.createElement('div');
        grid.className = 'tempel-dna-sources__grid';
        var labels = {
            website: ['Website', '◎'],
            content: ['Website-inhoud', '≡'],
            acf: ['Websitevelden', '▦'],
            taxonomy: ['Onderwerpen', '#'],
            seo: ['SEO-data', '◈'],
            web: ['Externe research', '↗']
        };
        sources.forEach(function (source) {
            if (!source || !source.title) return;
            var type = labels[source.type] ? source.type : 'web';
            var meta = labels[type];
            var item = document.createElement(source.url ? 'a' : 'div');
            item.className = 'tempel-dna-source-card tempel-dna-source-card--' + type;
            if (source.url) {
                item.href = source.url;
                item.target = '_blank';
                item.rel = 'noopener noreferrer';
            }
            var badge = document.createElement('span');
            badge.className = 'tempel-dna-source-card__type';
            badge.textContent = meta[1] + '  ' + meta[0];
            var heading;
            heading = document.createElement('strong');
            heading.className = 'tempel-dna-source-card__title';
            heading.textContent = source.title;
            item.appendChild(badge);
            item.appendChild(heading);
            if (source.detail) {
                var detail = document.createElement('span');
                detail.className = 'tempel-dna-source-detail';
                detail.textContent = source.detail;
                item.appendChild(detail);
            }
            grid.appendChild(item);
        });
        container.appendChild(header);
        container.appendChild(grid);
        container.hidden = !grid.children.length;
    }

    function parseTags(value) {
        var seen = {};
        return String(value || '').split(',').map(function (tag) { return tag.trim(); }).filter(function (tag) {
            var key = tag.toLowerCase();
            if (!tag || seen[key]) return false;
            seen[key] = true;
            return true;
        }).slice(0, 20);
    }

    function initTagEditor(field) {
        if (!field || field.dataset.tagEditor === 'ready') return;
        field.dataset.tagEditor = 'ready';
        field.classList.add('tempel-dna-tags__value');
        var editor = document.createElement('div');
        editor.className = 'tempel-dna-tag-editor';
        var list = document.createElement('div');
        list.className = 'tempel-dna-tag-list';
        var input = document.createElement('input');
        input.type = 'text';
        input.className = 'tempel-dna-tag-input';
        input.placeholder = 'Tag toevoegen…';
        input.setAttribute('aria-label', 'Tag toevoegen');
        editor.appendChild(list);
        editor.appendChild(input);
        field.insertAdjacentElement('afterend', editor);

        function render() {
            list.innerHTML = '';
            parseTags(field.value).forEach(function (tag) {
                var chip = document.createElement('span');
                chip.className = 'tempel-dna-tag';
                var text = document.createElement('span');
                text.textContent = tag;
                var remove = document.createElement('button');
                remove.type = 'button';
                remove.setAttribute('aria-label', tag + ' verwijderen');
                remove.textContent = '×';
                remove.addEventListener('click', function () {
                    field.value = parseTags(field.value).filter(function (value) { return value !== tag; }).join(', ');
                    field.dispatchEvent(new Event('change', { bubbles: true }));
                });
                chip.appendChild(text);
                chip.appendChild(remove);
                list.appendChild(chip);
            });
        }

        function add(refocus) {
            var additions = parseTags(input.value);
            if (!additions.length) return;
            field.value = parseTags(field.value).concat(additions).filter(function (tag, index, tags) {
                return tags.map(function (item) { return item.toLowerCase(); }).indexOf(tag.toLowerCase()) === index;
            }).slice(0, 20).join(', ');
            input.value = '';
            field.dispatchEvent(new Event('change', { bubbles: true }));
            if (refocus) input.focus();
        }

        input.addEventListener('keydown', function (event) {
            if (event.key === 'Enter' || event.key === ',') {
                event.preventDefault();
                add(true);
            } else if (event.key === 'Backspace' && input.value === '') {
                var tags = parseTags(field.value);
                if (tags.length) {
                    tags.pop();
                    field.value = tags.join(', ');
                    field.dispatchEvent(new Event('change', { bubbles: true }));
                }
            }
        });
        input.addEventListener('blur', function () { add(false); });
        field.addEventListener('change', render);
        render();
    }

    function renderProposal() {
        var section = document.getElementById('tempel-dna-proposal');
        var list = document.getElementById('tempel-dna-proposal-list');
        var count = document.getElementById('tempel-dna-proposal-count');
        if (!section || !list) return;
        var proposal = proposalDna();
        var available = Object.keys(tempelDNA.fields).filter(function (key) { return String(proposal[key] || '').trim(); });
        section.hidden = !available.length;
        list.innerHTML = '';
        if (count) count.textContent = available.length + (available.length === 1 ? ' suggestie' : ' suggesties');
        if (!available.length) return;

        Object.keys(tempelDNA.groups || {}).forEach(function (groupKey) {
            var keys = available.filter(function (key) { return tempelDNA.fields[key].group === groupKey; });
            if (!keys.length) return;
            var group = document.createElement('details');
            group.className = 'tempel-dna-proposal-group';
            var summary = document.createElement('summary');
            var heading = document.createElement('strong');
            heading.textContent = tempelDNA.groups[groupKey].number + '. ' + tempelDNA.groups[groupKey].label;
            var groupCount = document.createElement('span');
            groupCount.textContent = keys.length + (keys.length === 1 ? ' suggestie' : ' suggesties');
            summary.appendChild(heading);
            summary.appendChild(groupCount);
            group.appendChild(summary);
            var content = document.createElement('div');
            content.className = 'tempel-dna-proposal-group__content';
            keys.forEach(function (key) {
                var card = document.createElement('article');
                card.className = 'tempel-dna-proposal-card';
                var title = document.createElement('strong');
                title.textContent = tempelDNA.fields[key].label;
                var text = document.createElement('p');
                text.textContent = proposal[key];
                card.appendChild(title);
                card.appendChild(text);
                var tags = parseTags(proposal[key + '_tags']);
                if (tags.length) {
                    var tagList = document.createElement('div');
                    tagList.className = 'tempel-dna-proposal-tags';
                    tags.forEach(function (tag) {
                        var chip = document.createElement('span');
                        chip.textContent = tag;
                        tagList.appendChild(chip);
                    });
                    card.appendChild(tagList);
                }
                var actions = document.createElement('div');
                actions.className = 'tempel-dna-proposal-card__actions';
                var replace = document.createElement('button');
                replace.type = 'button';
                replace.className = 'button button-primary';
                replace.textContent = 'Overnemen';
                replace.addEventListener('click', function () { setDnaValues(proposal, key, 'replace'); clearProposal(key); });
                actions.appendChild(replace);
                var current = document.getElementById('tempel_dna_' + key);
                if (current && current.value.trim()) {
                    var append = document.createElement('button');
                    append.type = 'button';
                    append.className = 'button button-secondary';
                    append.textContent = 'Aanvullen';
                    append.addEventListener('click', function () { setDnaValues(proposal, key, 'append'); clearProposal(key); });
                    actions.appendChild(append);
                }
                var skip = document.createElement('button');
                skip.type = 'button';
                skip.className = 'button button-link-delete';
                skip.textContent = 'Overslaan';
                skip.addEventListener('click', function () { clearProposal(key); });
                actions.appendChild(skip);
                card.appendChild(actions);
                content.appendChild(card);
            });
            group.appendChild(content);
            list.appendChild(group);
        });
    }

    function generate(options) {
        options = options || {};
        var focusKey = options.focusKey || '';
        var button = options.button || document.getElementById('tempel-dna-generate');
        if (!button || button.disabled) return;
        button.disabled = true;
        var startedAt = Date.now();
        status(focusKey ? 'Het DNA-onderdeel wordt opnieuw onderzocht.' : 'Het diepe DNA-onderzoek is gestart.');
        progress({ phase: focusKey ? 'Wijziging en bronnen voorbereiden' : 'Onderzoek voorbereiden', progress: 5 }, startedAt);
        showSources([]);

        wp.apiFetch({
            path: tempelDNA.startPath,
            method: 'POST',
            headers: { 'X-WP-Nonce': tempelDNA.nonce },
            data: {
                direction: document.getElementById('tempel-dna-direction').value.trim(),
                use_trends: document.getElementById('tempel-dna-use-trends').checked,
                current: currentDna(),
                merge_mode: focusKey ? 'augment' : (document.getElementById('tempel-dna-merge-mode') || {}).value || 'augment',
                focus_key: focusKey,
                change_note: options.changeNote || ''
            }
        }).then(function (response) {
            if (!response || !response.job_id) throw new Error('De achtergrondtaak kon niet worden gestart.');
            poll(response.job_id, startedAt, button, focusKey);
        }).catch(function (error) {
            finishProgress();
            status(error && error.message ? error.message : 'Het DNA-voorstel kon niet worden gemaakt.', 'error');
            button.disabled = false;
        });
    }

    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('.tempel-dna-group').forEach(function (group) {
            group.addEventListener('toggle', function () {
                if (!group.open) return;
                document.querySelectorAll('.tempel-dna-group[open]').forEach(function (other) {
                    if (other !== group) other.open = false;
                });
            });
        });
        var button = document.getElementById('tempel-dna-generate');
        if (button) button.addEventListener('click', function () { generate(); });
        document.querySelectorAll('.tempel-dna-update-toggle').forEach(function (toggle) {
            toggle.addEventListener('click', function () {
                var panel = document.querySelector('[data-dna-panel="' + toggle.dataset.dnaKey + '"]');
                if (!panel) return;
                panel.hidden = !panel.hidden;
                toggle.setAttribute('aria-expanded', panel.hidden ? 'false' : 'true');
                if (!panel.hidden) panel.querySelector('textarea').focus();
            });
        });
        document.querySelectorAll('.tempel-dna-update-run').forEach(function (run) {
            run.addEventListener('click', function () {
                var panel = document.querySelector('[data-dna-panel="' + run.dataset.dnaKey + '"]');
                var note = panel ? panel.querySelector('textarea').value.trim() : '';
                generate({ focusKey: run.dataset.dnaKey, changeNote: note, button: run });
            });
        });
        document.querySelectorAll('#tmpl_dna_settings textarea').forEach(function (field) {
            autoSize(field);
            field.addEventListener('input', function () {
                autoSize(field);
                var row = field.closest('.tempel-ai-dna-field');
                var reviewed = row ? row.querySelector('.tempel-dna-reviewed input') : null;
                if (reviewed && reviewed.checked) {
                    reviewed.checked = false;
                    reviewed.dispatchEvent(new Event('change', { bubbles: true }));
                }
                updateGroupStatuses();
            });
        });
        document.querySelectorAll('.tempel-dna-reviewed input').forEach(function (field) {
            field.addEventListener('change', updateGroupStatuses);
        });
        document.querySelectorAll('#tmpl_dna_settings .tempel-dna-tags > input').forEach(initTagEditor);
        var acceptEmpty = document.getElementById('tempel-dna-accept-empty');
        if (acceptEmpty) acceptEmpty.addEventListener('click', function () {
            var proposal = proposalDna();
            Object.keys(tempelDNA.fields).forEach(function (key) {
                var current = document.getElementById('tempel_dna_' + key);
                if (current && !current.value.trim() && String(proposal[key] || '').trim()) {
                    setDnaValues(proposal, key, 'replace');
                    clearProposal(key);
                }
            });
        });
        var acceptAll = document.getElementById('tempel-dna-accept-all');
        if (acceptAll) acceptAll.addEventListener('click', function () {
            if (!window.confirm('Alle bestaande DNA-antwoorden vervangen door dit voorstel?')) return;
            setDnaValues(proposalDna(), '', 'replace');
            clearProposal();
        });
        var discardAll = document.getElementById('tempel-dna-discard-all');
        if (discardAll) discardAll.addEventListener('click', function () {
            if (!window.confirm('Dit volledige voorstel verwijderen?')) return;
            clearProposal();
        });
        renderProposal();
        updateGroupStatuses();
    });
}());
