// Firestore-only client implementation using modular v9 SDK via ESM imports.
import { initializeApp } from 'https://www.gstatic.com/firebasejs/9.23.0/firebase-app.js';
import { getFirestore, collection, getDocs, query, orderBy, limit as qLimit } from 'https://www.gstatic.com/firebasejs/9.23.0/firebase-firestore.js';

// ----- DOM -----
const article_root = document.getElementById('articles');

// ----- Helpers -----
function escapeHTML(str) {
  return str.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
}

function formatBodyHTML(text) {
  const raw = (text || '').toString();
  return escapeHTML(raw.replaceAll('%%', "'") )
    .replace(/\*\*(.+?)\*\*/g, (m, p1) => `<strong>${p1}</strong>`) // bold
    .replace(/\r\n|\r|\n/g, '<br>');
}

function domainFromHostname(hostname) {
  const h = hostname.replace(/^www\./, '');
  const parts = h.split('.');
  if (parts.length <= 2) return h;
  // Heuristic eTLD+1 (not perfect for all public suffixes, but good enough here)
  return parts.slice(-2).join('.');
}

function parseSources(sourcesInput) {
  // Returns array of { href, text }
  const out = [];
  const pushIfValid = (href, text) => {
    try {
      const u = new URL(href);
      const hostText = domainFromHostname(u.hostname);
      out.push({ href: u.toString(), text: hostText });
    } catch { /* ignore invalid */ }
  };

  const fromString = (str) => {
    const trimmed = str.trim();
    // Support "Label (https://url)"
    const m = trimmed.match(/^(.*)\((https?:\/\/[^)]+)\)\s*$/);
    if (m) {
      const label = m[1].trim().replace(/[\-\u2013\u2014]\s*$/, '').trim();
      pushIfValid(m[2].trim(), label || undefined);
      return;
    }
    pushIfValid(trimmed);
  };

  if (Array.isArray(sourcesInput)) {
    sourcesInput.forEach((item) => { if (item) fromString(String(item)); });
  } else if (typeof sourcesInput === 'string') {
    const s = sourcesInput.trim();
    if (s.startsWith('[') && s.endsWith(']')) {
      try { const parsed = JSON.parse(s); if (Array.isArray(parsed)) parsed.forEach(v => fromString(String(v))); else fromString(s); }
      catch { fromString(s); }
    } else if (/\n/.test(s)) {
      s.split(/\n+/).map(x => x.trim()).filter(Boolean).forEach(fromString);
    } else if (s.includes(',') || s.includes('|')) {
      s.split(/[|,]/).map(x => x.trim()).filter(Boolean).forEach(fromString);
    } else {
      fromString(s);
    }
  }

  // de-duplicate by href
  const seen = new Set();
  return out.filter(x => { if (!x || !x.href) return false; if (seen.has(x.href)) return false; seen.add(x.href); return true; });
}

function normalizeDate(dateVal) {
  if (!dateVal) return '';
  if (typeof dateVal === 'object' && typeof dateVal.toDate === 'function') {
    return dateVal.toDate().toISOString().slice(0, 10);
  }
  if (typeof dateVal === 'string') {
    const ddmmyyyy = dateVal.match(/^(\d{2})-(\d{2})-(\d{4})$/);
    if (ddmmyyyy) {
      const [, dd, mm, yyyy] = ddmmyyyy;
      return `${yyyy}-${mm}-${dd}`; // normalized for display
    }
  }
  return dateVal;
}

function normalizeArticle(docId, data) {
  return {
    title: data.title || '',
    bodyHTML: formatBodyHTML(data.content || data.text_body || data.textBody),
    date: normalizeDate(data.date),
    sources: parseSources(data.sources)
  };
}

function buildArticleElement(a) {
  const root = document.createElement('div');
  root.className = 'article_root mb-2p';

  // Header row: title (left) and date (top-right)
  const header = document.createElement('div');
  header.className = 'article_header mb-2p';

  const title = document.createElement('h2');
  title.className = 'article_title';
  title.textContent = a.title;

  const date = document.createElement('p');
  date.className = 'article_date';
  if (a.date) {
    try {
      const d = new Date(a.date);
      date.textContent = isNaN(d.getTime()) ? a.date : d.toLocaleDateString(undefined, { year: 'numeric', month: 'short', day: '2-digit' });
    } catch { date.textContent = a.date; }
  }
  header.append(title);
  if (date.textContent) header.append(date);

  const body = document.createElement('div');
  body.className = 'article_body mb-2p';
  body.innerHTML = a.bodyHTML;

  const sourcesWrapper = document.createElement('div');
  if (a.sources.length) {
    sourcesWrapper.className = 'article_sources mb-2p';
    a.sources.forEach((src, i) => {
      const link = document.createElement('a');
      link.href = src.href;
      link.textContent = src.text || 'Source ' + (i + 1);
      link.target = '_blank';
      link.rel = 'noopener noreferrer';
      link.className = 'article_source_link';
      sourcesWrapper.append(link);
      if (i < a.sources.length - 1) sourcesWrapper.append(document.createTextNode(' '));
    });
  } else {
    const none = document.createElement('span');
    none.textContent = 'No sources';
    sourcesWrapper.append(none);
  }

  root.append(header);
  root.append(body, sourcesWrapper);
  return root;
}

function renderArticles(list) {
  article_root.innerHTML = '';
  list.forEach(a => article_root.append(buildArticleElement(a)));
}

async function fetchNewsletters(db, limit) {
  const col = collection(db, 'newsletters');
  // Single-field order avoids composite index requirement
  const qRef = query(col, orderBy('date', 'desc'), qLimit(limit));
  return await getDocs(qRef);
}

// ----- Main -----
async function init() {
  if (!window.FIREBASE_CONFIG) {
    article_root.textContent = 'Missing FIREBASE_CONFIG. Please set window.FIREBASE_CONFIG in index.html.';
    return;
  }
  try {
    const app = initializeApp(window.FIREBASE_CONFIG);
    const db = getFirestore(app);
    const limit = window.ARTICLE_LIMIT || 10;
    const snap = await fetchNewsletters(db, limit);
    const items = [];
    snap.forEach(ds => items.push(normalizeArticle(ds.id, ds.data() || {})));
    renderArticles(items);
  } catch (e) {
    if (e && e.code === 'permission-denied') {
      console.error('[Firestore] Permission denied reading newsletters. Check your rules for /newsletters/{docId}.', e);
      article_root.textContent = 'Permission denied loading newsletters. Update Firestore rules to allow read on newsletters.';
    } else {
      console.error('[Firestore] Failed to load newsletters:', e);
      article_root.textContent = 'Error loading newsletters: ' + (e.message || e);
    }
  }
}

init();
