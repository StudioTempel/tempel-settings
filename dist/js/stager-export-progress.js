(function () {
    'use strict';
    const form = document.getElementById('stager-export-form');
    if (!form || !window.fetch || !window.ReadableStream) return;
    const button = form.querySelector('button[type="submit"], button:not([type])');
    const panel = document.getElementById('stager-export-progress');
    const message = document.getElementById('stager-export-message');
    const bar = document.getElementById('stager-export-bar');
    let running = false;

    function fail(text) {
        running = false;
        button.disabled = false;
        button.textContent = 'Opnieuw proberen';
        message.textContent = text;
        bar.hidden = true;
    }

    function receive(line) {
        if (!line.trim()) return;
        const data = JSON.parse(line);
        if (data.type === 'error') { fail(data.message); return; }
        if (data.type === 'progress') {
            message.textContent = data.message;
            if (Number.isFinite(data.percent)) { bar.value = Math.max(0, Math.min(100, data.percent)); }
            else { bar.removeAttribute('value'); }
            return;
        }
        if (data.type === 'done') {
            running = false;
            bar.value = 100;
            message.textContent = data.message + (data.size ? ' Grootte: ' + data.size + '.' : '');
            button.hidden = true;
            const link = document.createElement('a');
            link.className = 'button button-primary';
            link.href = data.url;
            link.textContent = 'Download pakket';
            link.addEventListener('click', () => { link.textContent = 'Download gestart'; link.classList.remove('button-primary'); });
            panel.append(link);
            return;
        }
        throw new Error('Onbekend voortgangsbericht.');
    }

    form.addEventListener('submit', async event => {
        event.preventDefault();
        if (running) return;
        running = true; button.disabled = true; button.textContent = 'Pakket wordt gemaakt…';
        panel.hidden = false; bar.hidden = false; bar.removeAttribute('value'); message.textContent = 'Website controleren…';
        try {
            const endpoint = form.getAttribute('action');
            if (!endpoint) throw new Error('De downloadroute ontbreekt. Vernieuw de pagina en probeer opnieuw.');
            const response = await fetch(endpoint, {method: 'POST', credentials: 'same-origin', body: new FormData(form)});
            if (!response.ok) throw new Error('De server antwoordt met HTTP ' + response.status + '.');
            if (!response.body || !(response.headers.get('content-type') || '').includes('application/x-ndjson')) throw new Error('De server gaf geen geldige voortgang terug. Vernieuw de pagina en probeer opnieuw.');
            const reader = response.body.getReader(); const decoder = new TextDecoder(); let pending = ''; let finished = false;
            while (true) {
                const chunk = await reader.read();
                pending += chunk.done ? decoder.decode() : decoder.decode(chunk.value, {stream: true});
                let newline;
                while ((newline = pending.indexOf('\n')) !== -1) { const line = pending.slice(0, newline); pending = pending.slice(newline + 1); receive(line); if (line.includes('"type":"done"') || line.includes('"type":"error"')) finished = true; }
                if (chunk.done) { if (pending.trim()) receive(pending); break; }
            }
            if (!finished && running) throw new Error('Het maken is gestopt zonder eindbevestiging.');
        } catch (error) { fail(error.message); }
    });
})();
