#!/usr/bin/env node
/**
 * Build script: Fetch articles from Firestore using firebase-admin and emit a static JSON file.
 * Usage: (with env vars loaded) node scripts/build_articles.js [limit]
 * Env required:
 *  GC_PROJECT_ID
 *  FIREBASE_CLIENT_EMAIL
 *  FIREBASE_PRIVATE_KEY  (literal with \n newlines OR escaped \n sequences)
 * Optional:
 *  ARTICLES_LIMIT (default 25)
 * Output:
 *  dist/articles.json
 */

import fs from 'fs';
import path from 'path';
import { fileURLToPath } from 'url';
import { dirname } from 'path';
import { initializeApp, cert, getApps } from 'firebase-admin/app';
import { getFirestore } from 'firebase-admin/firestore';

const __filename = fileURLToPath(import.meta.url);
const __dirname = dirname(__filename);

function requireEnv(key) {
  const v = process.env[key];
  if (!v) {
    console.error(`Missing required env var ${key}`);
    process.exit(1);
  }
  return v;
}

function normalizeArticle(doc) {
  const d = doc.data() || {};
  let dateVal = d.date;
  if (dateVal && typeof dateVal === 'object' && typeof dateVal.toDate === 'function') {
    dateVal = dateVal.toDate().toISOString().slice(0, 10);
  }
  const raw = (d.text_body || d.textBody || '').toString();
  let processed = raw.replaceAll('%%', "'");
  return {
    id: d.id ?? doc.id,
    title: d.title || '',
    text_body: processed,
    sources: d.sources || [],
    date: dateVal || ''
  };
}

async function main() {
  const projectId = requireEnv('GC_PROJECT_ID');
  const clientEmail = requireEnv('FIREBASE_CLIENT_EMAIL');
  let privateKey = requireEnv('FIREBASE_PRIVATE_KEY');
  // Support escaped newlines
  if (privateKey.includes('\\n')) privateKey = privateKey.replace(/\\n/g, '\n');

  if (!getApps().length) {
    initializeApp({ credential: cert({ projectId, clientEmail, privateKey }) });
  }
  const db = getFirestore();
  const limitArg = parseInt(process.argv[2], 10);
  const limit = !isNaN(limitArg) ? limitArg : parseInt(process.env.ARTICLES_LIMIT || '25', 10);

  const col = db.collection('articles');
  // Primary ordering by date desc then id desc (if exists)
  let snapshot;
  try {
    snapshot = await col.orderBy('date', 'desc').orderBy('id', 'desc').limit(limit).get();
  } catch (e) {
    snapshot = await col.orderBy('date', 'desc').limit(limit).get();
  }

  const out = snapshot.docs.map(normalizeArticle);
  const distDir = path.join(__dirname, '..', 'dist');
  fs.mkdirSync(distDir, { recursive: true });
  const outPath = path.join(distDir, 'articles.json');
  fs.writeFileSync(outPath, JSON.stringify(out, null, 2), 'utf8');
  console.log(`Wrote ${out.length} articles to ${outPath}`);
}

main().catch(e => {
  console.error(e);
  process.exit(1);
});
