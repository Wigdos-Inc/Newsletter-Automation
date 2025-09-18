// Firestore-only client implementation using modular v9 SDK via ESM imports.
import { initializeApp } from 'https://www.gstatic.com/firebasejs/9.23.0/firebase-app.js';
import { getFirestore, collection, getDocs, query, orderBy, limit as qLimit, doc, getDoc } from 'https://www.gstatic.com/firebasejs/9.23.0/firebase-firestore.js';

const article_root = document.getElementById('articles');

function escapeHTML(str){
  return str.replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');
}

function normalizeArticle(obj){
  const raw = (obj.text_body || obj.textBody || '').toString();
  let processed = raw.replaceAll('%%', "'");
  processed = escapeHTML(processed)
    .replace(/\*\*(.+?)\*\*/g,(m,p1)=>`<strong>${p1}</strong>`) // bold
    .replace(/\r\n|\r|\n/g,'<br>');
  let links = [];
  const s = obj.sources;
  if (Array.isArray(s)) links = s; else if (typeof s === 'string') {
    const trimmed = s.trim();
    if (trimmed.startsWith('[') && trimmed.endsWith(']')) {
      try { const parsed = JSON.parse(trimmed); if (Array.isArray(parsed)) links = parsed; else links=[trimmed]; } catch { links=[trimmed]; }
    } else if (/\n/.test(trimmed)) links = trimmed.split(/\n+/).map(x=>x.trim()).filter(Boolean);
    else if (trimmed.includes(',')) links = trimmed.split(',').map(x=>x.trim()).filter(Boolean);
    else if (trimmed.includes('|')) links = trimmed.split('|').map(x=>x.trim()).filter(Boolean);
    else links = [trimmed];
  }
  const urlSet = new Set();
  const validLinks = links.filter(l=>{ if(!l) return false; try { new URL(l); } catch { return false; } if(urlSet.has(l)) return false; urlSet.add(l); return true; });
  return {
    id: obj.id,
    title: obj.title || '',
    bodyHTML: processed,
    date: obj.date || '',
    sources: validLinks
  };
}

function renderArticles(arr){
  article_root.innerHTML='';
  arr.forEach(a=>{
    const root=document.createElement('div');
    root.className='article_root mb-2p';
    const idx=document.createElement('p'); idx.className='article_index mb-2p bg_light p-1p'; idx.textContent=a.id;
    const title=document.createElement('h2'); title.className='article_title mb-2p bg_light p-1p'; title.textContent=a.title;
    const date=document.createElement('p'); date.className='article_date mb-2p';
    if(a.date){ try { const d=new Date(a.date); if(!isNaN(d.getTime())) date.textContent=d.toLocaleDateString(undefined,{year:'numeric',month:'short',day:'2-digit'}); else date.textContent=a.date; } catch { date.textContent=a.date; } }
    const body=document.createElement('div'); body.className='article_body mb-2p bg_light p-1p'; body.innerHTML=a.bodyHTML;
    const sourcesWrapper=document.createElement('div');
    if(a.sources.length){
      sourcesWrapper.className='article_sources mb-2p bg_light p-1p';
      a.sources.forEach((href,i)=>{ const link=document.createElement('a'); link.href=href; try { link.textContent=new URL(href).hostname.replace(/^www\./,''); } catch { link.textContent='Source '+(i+1);} link.target='_blank'; link.rel='noopener noreferrer'; link.className='article_source_link'; sourcesWrapper.append(link); if(i<a.sources.length-1) sourcesWrapper.append(document.createTextNode(' ')); });
    } else { const none=document.createElement('span'); none.textContent='No sources'; sourcesWrapper.append(none); }
    root.append(idx,title); if(date.textContent) root.append(date); root.append(body,sourcesWrapper); article_root.append(root);
  });
}

// Note: Removed server-side firebase-admin logic; this file now remains purely client-side.

if (window.FIREBASE_CONFIG) {
  const app = initializeApp(window.FIREBASE_CONFIG);
  const db = getFirestore(app);
  const lim = window.ARTICLE_LIMIT || 10;
  const baseCol = collection(db, 'articles');
  // Insert a container for the newsletters/news test value (field 'test')
  let testContainer = document.getElementById('newsletter-test');
  if(!testContainer){
    testContainer = document.createElement('div');
    testContainer.id = 'newsletter-test';
    testContainer.style.margin = '12px 0';
    testContainer.style.padding = '8px';
    testContainer.style.background = '#222';
    testContainer.style.color = '#fff';
    testContainer.style.fontFamily = 'system-ui, Arial, sans-serif';
    testContainer.textContent = 'Loading test value...';
    article_root.before(testContainer);
  }

  async function loadTestField(){
    try {
      const ref = doc(db, 'newsletters', 'news'); // document path newsletters/news
      console.debug('[loadTestField] Attempting to read document path: newsletters/news');
      const snap = await getDoc(ref);
      if(!snap.exists()){
        console.warn('[loadTestField] Document newsletters/news does not exist.');
        testContainer.textContent = 'No newsletters/news document found.';
        return;
      }
      const data = snap.data() || {};
      if(Object.prototype.hasOwnProperty.call(data,'test')){
        console.debug('[loadTestField] Retrieved field test with value:', data.test);
        testContainer.textContent = 'test: ' + String(data.test);
      } else {
        console.warn('[loadTestField] Field "test" missing on newsletters/news. Available keys:', Object.keys(data));
        testContainer.textContent = 'Field "test" not present on newsletters/news.';
      }
    } catch(e){
      // Provide granular diagnostics
      const code = e && e.code;
      if(code === 'permission-denied'){
        console.error('[loadTestField] Permission denied reading newsletters/news. This means current Firestore rules do not allow read access for unauthenticated users (or this user).', e);
        testContainer.textContent = 'Permission denied reading newsletters/news. Update Firestore rules to allow read.';
      } else {
        console.error('[loadTestField] Unexpected error reading newsletters/news:', e);
        testContainer.textContent = 'Error loading test field (' + (code || 'unknown') + ').';
      }
      // Extra debug context
      console.debug('[loadTestField] FIREBASE_CONFIG projectId:', window.FIREBASE_CONFIG && window.FIREBASE_CONFIG.projectId);
    }
  }
  let wantDualOrder = true;
  let snap;
  try {
    // Attempt compound ordering date desc + id desc
    let qRef = query(baseCol, orderBy('date', 'desc'), orderBy('id', 'desc'), qLimit(lim));
    snap = await getDocs(qRef);
  } catch (e) {
    // Firestore index error code: failed-precondition
    if (e && e.code === 'failed-precondition') {
      wantDualOrder = false;
      console.warn('[Firestore] Missing composite index for (date desc, id desc). Retrying with single order.');
      // Fallback: only order by date
      const qRef = query(baseCol, orderBy('date', 'desc'), qLimit(lim));
      snap = await getDocs(qRef);
      // Provide UI hint to create index
      const hint = document.createElement('div');
      hint.className = 'index-hint';
      hint.style.background = '#332';
      hint.style.color = '#f5c06e';
      hint.style.padding = '8px';
      hint.style.margin = '8px 0';
      hint.style.fontSize = '0.85rem';
      hint.innerHTML = 'Using fallback ordering (missing composite index date desc + id desc). Create index in Firebase Console > Firestore Indexes for collection "articles" with fields <code>date desc, id desc</code> to enable secondary ordering.';
      article_root.before(hint);
    } else {
      console.error(e);
      article_root.textContent = 'Error loading articles: ' + (e.message || e);
      throw e;
    }
  }
  if (snap) {
    const out = [];
    snap.forEach(doc => {
      const d = doc.data() || {};
      let dateVal = d.date;
      if (dateVal && typeof dateVal === 'object' && typeof dateVal.toDate === 'function') {
        dateVal = dateVal.toDate().toISOString().slice(0, 10);
      }
      out.push(normalizeArticle({
        id: d.id ?? doc.id,
        title: d.title,
        text_body: d.text_body || d.textBody,
        sources: d.sources,
        date: dateVal
      }));
    });
    renderArticles(out);
  }
  // Always attempt to load the newsletters/news document test field after articles
  loadTestField();
} else {
  article_root.textContent = 'Missing FIREBASE_CONFIG. Please set window.FIREBASE_CONFIG in index.html.';
}
