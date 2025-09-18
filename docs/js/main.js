const article_root = document.getElementById('articles');

function generate_article(obj) {
    const root = document.createElement('div');
    const index = document.createElement('p');
    const titel = document.createElement('h2');
    const textbody = document.createElement('textarea');
    const bron = document.createElement('a');

    index.innerHTML = obj['id'];
    titel.innerHTML = obj['title'];
    textbody.textContent = obj['text_body'];
    bron.href = obj['sources'];
    bron.innerHTML = obj['sources'];

    root.setAttribute('class', 'article_root mb-2p');
    index.setAttribute('class', 'article_index mb-2p bg_light p-1p');
    titel.setAttribute('class', 'article_title mb-2p bg_light p-1p');
    textbody.setAttribute('class', 'article_body mb-2p bg_light p-1p');
    textbody.setAttribute('readonly', '');
    textbody.style.resize = 'none';
    bron.setAttribute('class', 'article_source mb-2p bg_light p-1p');

    root.append(index);
    root.append(titel);
    root.append(textbody);
    root.append(bron);
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
