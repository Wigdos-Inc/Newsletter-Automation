const article_root = document.getElementById('articles');

function generate_article(obj) {
    const root = document.createElement('div');
    const index = document.createElement('p');
    const title = document.createElement('h2');
    const dateEl = document.createElement('p');
    const textbody = document.createElement('textarea');
    const sourcesWrapper = document.createElement('div');

    index.textContent = obj['id'];
    title.textContent = obj['title'];
    let rawDate = obj['date'];
    if (rawDate) {
        try {
            const d = new Date(rawDate);
            if (!isNaN(d.getTime())) {
                const fmt = d.toLocaleDateString(undefined, {year:'numeric', month:'short', day:'2-digit'});
                dateEl.textContent = fmt;
            } else {
                dateEl.textContent = rawDate;
            }
        } catch(_) { dateEl.textContent = rawDate; }
    }
    textbody.textContent = obj['text_body'];

    // Parse sources: can be array, JSON string, or delimited string
    let rawSources = obj['sources'];
    let links = [];
    if (Array.isArray(rawSources)) {
        links = rawSources;
    } else if (typeof rawSources === 'string') {
        const trimmed = rawSources.trim();
        if (trimmed.startsWith('[') && trimmed.endsWith(']')) {
            try {
                const parsed = JSON.parse(trimmed);
                if (Array.isArray(parsed)) links = parsed; else links = [trimmed];
            } catch (_) {
                links = [trimmed];
            }
        } else if (trimmed.includes('\n')) {
            links = trimmed.split(/\n+/).map(s => s.trim()).filter(Boolean);
        } else if (trimmed.includes(',')) {
            links = trimmed.split(',').map(s => s.trim()).filter(Boolean);
        } else if (trimmed.includes('|')) {
            links = trimmed.split('|').map(s => s.trim()).filter(Boolean);
        } else {
            links = [trimmed];
        }
    }

    // Basic URL validation and unique filtering
    const urlSet = new Set();
    const validLinks = links.filter(l => {
        if (!l) return false;
        try { new URL(l); } catch { return false; }
        if (urlSet.has(l)) return false;
        urlSet.add(l);
        return true;
    });

    if (validLinks.length === 0) {
        const none = document.createElement('span');
        none.textContent = 'No sources';
        sourcesWrapper.append(none);
    } else {
        sourcesWrapper.setAttribute('class', 'article_sources mb-2p bg_light p-1p');
        validLinks.forEach((href, i) => {
            const a = document.createElement('a');
            a.href = href;
            let hostText = '';
            try {
                const u = new URL(href);
                hostText = u.hostname.replace(/^www\./,'');
            } catch { hostText = `Source ${i+1}`; }
            a.textContent = hostText;
            a.title = href;
            a.target = '_blank';
            a.rel = 'noopener noreferrer';
            a.className = 'article_source_link';
            sourcesWrapper.append(a);
            if (i < validLinks.length - 1) {
                const sep = document.createElement('span');
                sep.textContent = ' '; // small gap
                sourcesWrapper.append(sep);
            }
        });
    }

    root.setAttribute('class', 'article_root mb-2p');
    index.setAttribute('class', 'article_index mb-2p bg_light p-1p');
    title.setAttribute('class', 'article_title mb-2p bg_light p-1p');
    if (dateEl.textContent) dateEl.setAttribute('class', 'article_date mb-2p');
    textbody.setAttribute('class', 'article_body mb-2p bg_light p-1p');
    textbody.setAttribute('readonly', '');
    textbody.style.resize = 'none';

    root.append(index);
    root.append(title);
    if (dateEl.textContent) root.append(dateEl);
    root.append(textbody);
    root.append(sourcesWrapper);
    article_root.append(root);
}

function renderArticles(arr) {
    if (!Array.isArray(arr)) return;
    article_root.innerHTML = '';
    for (let i = 0; i < arr.length; i++) {
        generate_article(arr[i]);
    }
}

async function loadArticles() {
    // If server-side injected variable exists (PHP mode), use it.
    if (typeof window.result !== 'undefined') {
        renderArticles(window.result);
        return;
    }
    // Otherwise try to fetch static JSON (GitHub Pages mode)
    try {
        const resp = await fetch('articles.json', {cache: 'no-store'});
        if (!resp.ok) throw new Error('HTTP ' + resp.status);
        const data = await resp.json();
        renderArticles(data);
    } catch (e) {
        console.error('Failed to load articles.json', e);
        const msg = document.createElement('p');
        msg.textContent = 'Unable to load articles at this time.';
        article_root.append(msg);
    }
}

loadArticles();
