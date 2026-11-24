(function () {
    'use strict';

    var timer = null;

    function text(id, value) {
        var element = document.getElementById(id);
        if (element) element.textContent = value || '';
    }

    function message(value, type) {
        var element = document.getElementById('tempel-optimization-message');
        if (!element) return;
        element.textContent = value || '';
        element.className = 'tempel-ui-field__help' + (type ? ' is-' + type : '');
    }

    function progress(job) {
        var box = document.getElementById('tempel-optimization-progress');
        if (!box) return;
        box.hidden = false;
        var value = Math.max(5, Math.min(100, Number(job.progress || 5)));
        box.querySelector('progress').value = value;
        text('tempel-optimization-phase', job.phase || 'Pagina analyseren');
        text('tempel-optimization-percent', value + '%');
    }

    function appendList(id, items) {
        var list = document.getElementById(id);
        if (!list) return;
        list.innerHTML = '';
        (items || []).forEach(function (item) {
            var li = document.createElement('li');
            li.textContent = item;
            list.appendChild(li);
        });
    }

    function appendCards(id, items, fields) {
        var list = document.getElementById(id);
        if (!list) return;
        list.innerHTML = '';
        (items || []).forEach(function (item) {
            var article = document.createElement('article');
            fields.forEach(function (field, index) {
                var value = String(item[field] || '').trim();
                if (!value) return;
                var element = document.createElement(index === 0 ? 'strong' : 'p');
                element.textContent = value;
                if (field === 'priority') element.className = 'tempel-optimization-priority is-' + value.toLowerCase();
                article.appendChild(element);
            });
            list.appendChild(article);
        });
    }

    function render(result) {
        var section = document.getElementById('tempel-optimization-result');
        section.hidden = false;
        text('tempel-optimization-title', 'Verbeterplan voor ' + (result.page_title || 'pagina'));
        text('tempel-optimization-verdict', result.verdict || 'De scan is afgerond.');
        appendList('tempel-optimization-strengths', result.strengths);
        appendCards('tempel-optimization-issues', result.issues, ['priority', 'title', 'explanation']);
        appendCards('tempel-optimization-recommendations', result.recommendations, ['title', 'reason', 'proposal']);
        appendCards('tempel-optimization-rewrites', result.rewrite_sections, ['section', 'proposal']);
        var seo = document.getElementById('tempel-optimization-seo');
        seo.innerHTML = '';
        [['Zoekintentie', 'search_intent'], ['SEO-titel', 'title'], ['Metabeschrijving', 'meta_description'], ['URL', 'slug']].forEach(function (pair) {
            if (!result.seo || !result.seo[pair[1]]) return;
            var dt = document.createElement('dt');
            var dd = document.createElement('dd');
            dt.textContent = pair[0];
            dd.textContent = result.seo[pair[1]];
            seo.appendChild(dt); seo.appendChild(dd);
        });
        var edit = document.getElementById('tempel-optimization-edit');
        edit.hidden = !result.edit_url;
        if (result.edit_url) edit.href = result.edit_url;
        document.getElementById('tempel-optimization-progress').hidden = true;
        section.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }

    function poll(jobId, button) {
        wp.apiFetch({ path: tempelOptimization.statusPath + encodeURIComponent(jobId), method: 'GET', headers: { 'X-WP-Nonce': tempelOptimization.nonce } })
            .then(function (job) {
                progress(job);
                if (job.status === 'complete') {
                    button.disabled = false;
                    message('Scan afgerond. Er is niets automatisch gewijzigd.', 'success');
                    render(job.result || {});
                    return;
                }
                if (job.status === 'failed') throw new Error(job.error || 'De scan is mislukt.');
                timer = window.setTimeout(function () { poll(jobId, button); }, 1800);
            })
            .catch(function (error) {
                button.disabled = false;
                document.getElementById('tempel-optimization-progress').hidden = true;
                message(error && error.message ? error.message : 'De scan is mislukt.', 'error');
            });
    }

    document.addEventListener('DOMContentLoaded', function () {
        var button = document.getElementById('tempel-optimization-start');
        if (!button) return;
        button.addEventListener('click', function () {
            var postId = Number(document.getElementById('tempel-optimization-page').value || 0);
            if (!postId) { message('Kies eerst een pagina.', 'error'); return; }
            if (timer) window.clearTimeout(timer);
            button.disabled = true;
            document.getElementById('tempel-optimization-result').hidden = true;
            progress({ phase: 'Scan voorbereiden', progress: 5 });
            message('De scan is gestart. Dit kan enkele minuten duren.');
            wp.apiFetch({
                path: tempelOptimization.startPath,
                method: 'POST',
                headers: { 'X-WP-Nonce': tempelOptimization.nonce },
                data: { post_id: postId, focus: document.getElementById('tempel-optimization-focus').value.trim() }
            }).then(function (response) {
                if (!response || !response.job_id) throw new Error('De scan kon niet worden gestart.');
                poll(response.job_id, button);
            }).catch(function (error) {
                button.disabled = false;
                document.getElementById('tempel-optimization-progress').hidden = true;
                message(error && error.message ? error.message : 'De scan kon niet worden gestart.', 'error');
            });
        });
    });
}());
