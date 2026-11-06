(function () {
    'use strict';
    const action = document.querySelector('input[name="action"][value="tempel_stager_deploy"]');
    if (!action || !window.fetch || !window.ReadableStream) return;
    const form = action.form;
    let running = false;
    const warn = event => { if (running) { event.preventDefault(); event.returnValue = ''; } };
    window.addEventListener('beforeunload', warn);
    form.addEventListener('submit', async event => {
        event.preventDefault();
        if (running) return;
        running = true;
        const body = new FormData(form);
        body.set('stager_stream', '1');
        const panel = document.createElement('section');
        panel.className = 'tempel-ui-progress tempel-stager-live-progress';
        const header = document.createElement('div'); header.className = 'tempel-ui-progress__header';
        const title = document.createElement('h2'); title.className = 'tempel-ui-progress__phase'; title.textContent = 'Je website wordt overgezet'; title.tabIndex = -1;
        const message = document.createElement('p'); message.setAttribute('role', 'status'); message.setAttribute('aria-live', 'polite'); message.textContent = 'Verbinding maken…';
        const bar = document.createElement('progress'); bar.max = 100; bar.setAttribute('aria-label', 'Voortgang huidige stap');
        const percent = document.createElement('span'); percent.className = 'tempel-ui-progress__percent';
        const steps = [['connect', 'Verbinden'], ['repair', 'Ontbrekende bestanden herstellen'], ['check', 'Online WordPress controleren'], ['pack', 'Website inpakken'], ['upload', 'Pakket uploaden'], ['import', 'Database en bestanden importeren']];
        const list = document.createElement('ol');
        const items = steps.map(([, label]) => { const item = document.createElement('li'); item.textContent = label; item.style.marginBottom = '10px'; list.append(item); return item; });
        const note = document.createElement('p'); note.className = 'tempel-ui-progress__message'; note.textContent = 'Laat dit venster open. Bij inpakken en importeren is nog geen percentage beschikbaar. Meldingen kunnen door serverbuffering later verschijnen.';
        header.append(title, percent); panel.append(header, message, bar, list, note);
        form.before(panel); form.hidden = true; title.focus(); panel.scrollIntoView({block: 'start', behavior: 'smooth'});
        let terminal = false;
        function failure(text) {
            terminal = true; running = false;
            title.textContent = 'Verhuizing niet bevestigd'; message.textContent = text;
            bar.hidden = true; percent.textContent = '';
            note.textContent = 'Start niet automatisch opnieuw. Controleer de foutmelding en de online site voordat je verdergaat.';
        }
        function receive(line) {
            if (!line.trim() || terminal) return;
            const data = JSON.parse(line);
            if (data.type === 'error') { failure(data.message); return; }
            if (data.type === 'done') {
                terminal = true; running = false; title.textContent = 'Overdracht voltooid'; message.textContent = data.message;
                bar.value = 100; percent.textContent = '';
                items.forEach((item, index) => { item.textContent = '✓ ' + steps[index][1]; });
                const url = new URL(data.url);
                if (url.protocol === 'https:') {
                    const link = document.createElement('a'); link.href = url.href; link.textContent = 'Controleer je website'; link.className = 'button button-primary'; link.target = '_blank'; link.rel = 'noopener'; panel.append(link);
                }
                note.textContent = 'Log online in met je lokale account en sla de permalinks opnieuw op. Controleer ook formulieren, media en eventuele betalingen.';
                return;
            }
            if (data.type !== 'progress') throw new Error('Onbekend voortgangsbericht.');
            message.textContent = data.message;
            const position = steps.findIndex(([key]) => key === data.stage);
            items.forEach((item, index) => { item.textContent = (index < position ? '✓ ' : index === position ? '→ ' : '') + steps[index][1]; });
            if (data.stage === 'upload' && Number.isFinite(data.percent)) { bar.value = Math.max(0, Math.min(100, data.percent)); percent.textContent = 'Upload: ' + bar.value + '%'; }
            else { bar.removeAttribute('value'); percent.textContent = 'Bezig…'; }
        }
        try {
            // The WordPress routing input named "action" shadows form.action.
            const endpoint = form.getAttribute('action');
            if (!endpoint) throw new Error('Het verzendadres ontbreekt. Vernieuw de pluginpagina.');
            const response = await fetch(endpoint, {method: 'POST', credentials: 'same-origin', body});
            if (!response.ok) throw new Error('De lokale server antwoordt met HTTP ' + response.status + '. De verhuizing is niet bevestigd.');
            if (!response.body || !(response.headers.get('content-type') || '').includes('application/x-ndjson')) throw new Error('De lokale server gaf geen voortgangsstream terug (HTTP ' + response.status + '). Controleer of je nog bent ingelogd en of de nieuwste pluginversie actief is.');
            const reader = response.body.getReader(); const decoder = new TextDecoder(); let pending = '';
            while (true) {
                const {value, done} = await reader.read();
                pending += done ? decoder.decode() : decoder.decode(value, {stream: true});
                let newline;
                while ((newline = pending.indexOf('\n')) !== -1) { receive(pending.slice(0, newline)); pending = pending.slice(newline + 1); }
                if (done) { if (pending.trim()) receive(pending); break; }
            }
            if (!terminal) throw new Error('De verbinding is beëindigd zonder bevestiging. De import kan op de server nog actief zijn.');
        } catch (error) { if (!terminal) failure(error.message); }
        finally { running = false; }
    });
})();
