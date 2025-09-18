// Firestore-only client implementation using modular v9 SDK via ESM imports.
import { initializeApp } from 'https://www.gstatic.com/firebasejs/9.23.0/firebase-app.js';
import { getFirestore, collection, getDocs, query, orderBy, limit as qLimit } from 'https://www.gstatic.com/firebasejs/9.23.0/firebase-firestore.js';

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

async function initFirestore() {
  const admin = require('firebase-admin');
  if (!admin.apps.length) {
    const credsMissing = !(process.env.FIREBASE_CLIENT_EMAIL && process.env.FIREBASE_PRIVATE_KEY);
    if (credsMissing) throw new Error('Missing environment variables for Firebase Admin SDK');
    const { initializeApp, applicationDefault, cert } = require('firebase-admin/app');
    const { getFirestore } = require('firebase-admin/firestore');
    const serviceAccount = {
      projectId: process.env.GC_PROJECT_ID,
      clientEmail: process.env.FIREBASE_CLIENT_EMAIL,
      privateKey: process.env.FIREBASE_PRIVATE_KEY.replace(/\\n/g, '\n')
    };
    initializeApp({ credential: cert(serviceAccount) });
  }
  const db = getFirestore();
  const lim = window.ARTICLE_LIMIT || 10;
  let qRef = query(collection(db, 'articles'), orderBy('date', 'desc'));
  try {
    qRef = query(collection(db, 'articles'), orderBy('date', 'desc'), orderBy('id', 'desc'));
  } catch (_) {}
  qRef = query(qRef, qLimit(lim));
  const snap = await getDocs(qRef);
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

if (window.FIREBASE_CONFIG) {
  const app = initializeApp(window.FIREBASE_CONFIG);
  const db = getFirestore(app);
  const lim = window.ARTICLE_LIMIT || 10;
  let qRef = query(collection(db, 'articles'), orderBy('date', 'desc'));
  try {
    qRef = query(collection(db, 'articles'), orderBy('date', 'desc'), orderBy('id', 'desc'));
  } catch (_) {}
  qRef = query(qRef, qLimit(lim));
  const snap = await getDocs(qRef);
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
} else {
  throw new Error('Missing global FIREBASE_CONFIG');
}
