#!/usr/bin/env node
'use strict';
/**
 * build_articles.js
 * Replaces legacy PHP build. Fetches latest articles from Firestore and writes static assets into docs/.
 *
 * Env Vars:
 *  FIREBASE_PROJECT_ID (optional if service account provides it)
 *  FIREBASE_CLIENT_EMAIL
 *  FIREBASE_PRIVATE_KEY   (use GitHub secret; ensure newlines are preserved or replace \n)
 *  ARTICLE_LIMIT (default 5)
 *  BUILD_ALLOW_EMPTY=1 to allow zero articles without failing
 *
 * Usage:
 *  node scripts/build_articles.js
 */

const fs = require('fs');
const path = require('path');

function log(level, msg) {
  const ts = new Date().toISOString();
  const stream = level === 'ERROR' ? process.stderr : process.stdout;
  stream.write(`[${level}] ${msg}\n`);
}

async function initFirestore() {
  // Lazy import firebase-admin to keep startup fast if misconfigured
  const admin = require('firebase-admin');

  if (!admin.apps.length) {
    const credsMissing = !(process.env.FIREBASE_CLIENT_EMAIL && process.env.FIREBASE_PRIVATE_KEY);
    if (credsMissing) {
      log('ERROR', 'Missing FIREBASE_CLIENT_EMAIL or FIREBASE_PRIVATE_KEY env vars');
      throw new Error('Missing Firebase credentials');
    }
    let privateKey = process.env.FIREBASE_PRIVATE_KEY;
    // GitHub Actions often stores multi-line secrets with \n literals
    if (privateKey.includes('\\n')) privateKey = privateKey.replace(/\\n/g, '\n');
    admin.initializeApp({
      credential: admin.credential.cert({
        projectId: process.env.FIREBASE_PROJECT_ID || undefined,
        clientEmail: process.env.FIREBASE_CLIENT_EMAIL,
        privateKey
      })
    });
  }
  return admin.firestore();
}

function normalizeArticle(doc) {
  const data = doc.data() || {};
  // Map Firestore doc to expected JSON structure
  return {
    id: data.id ?? doc.id, // prefer stored numeric id else doc id string
    title: data.title || '',
    text_body: data.text_body || data.textBody || '',
    sources: data.sources ?? [],
    date: data.date ? (data.date.toDate ? data.date.toDate().toISOString().slice(0,10) : data.date) : ''
  };
}

async function fetchArticles(limit) {
  const db = await initFirestore();
  // Assume collection name 'articles'. Add composite index in console if ordering by date desc then id.
  let query = db.collection('articles').orderBy('date', 'desc');
  // Secondary order only if numeric id field exists; Firestore requires index for multi ordering.
  // We'll try-catch a second orderBy but not required.
  try { query = query.orderBy('id', 'desc'); } catch(_) {}
  query = query.limit(limit);
  const snap = await query.get();
  const arr = [];
  snap.forEach(doc => arr.push(normalizeArticle(doc)));
  return arr;
}

function writeIfChanged(filePath, content) {
  if (fs.existsSync(filePath)) {
    const prev = fs.readFileSync(filePath, 'utf8');
    if (prev === content) {
      log('INFO', `${path.basename(filePath)} unchanged; skipping`);
      return false;
    }
  }
  fs.writeFileSync(filePath, content);
  log('INFO', `Wrote ${filePath}`);
  return true;
}

async function main() {
  log('INFO', 'Starting Firestore static build...');
  const limitEnv = parseInt(process.env.ARTICLE_LIMIT || '5', 10);
  const ARTICLE_LIMIT = (Number.isFinite(limitEnv) && limitEnv > 0 && limitEnv < 5000) ? limitEnv : 5;
  let articles = [];
  let hadConnection = false;
  try {
    articles = await fetchArticles(ARTICLE_LIMIT);
    hadConnection = true;
    log('INFO', `Fetched ${articles.length} articles.`);
  } catch (err) {
    log('ERROR', `Failed to fetch articles: ${err.message}`);
  }

  const docsDir = path.join(__dirname, '..', 'docs');
  if (!fs.existsSync(docsDir)) fs.mkdirSync(docsDir, {recursive: true});

  // articles.json
  const articlesJson = JSON.stringify(articles, null, 4);
  writeIfChanged(path.join(docsDir, 'articles.json'), articlesJson);

  // build_log.json
  const buildLog = {
    timestamp_utc: new Date().toISOString(),
    article_count: articles.length,
    had_connection: hadConnection,
    firestore_project: process.env.FIREBASE_PROJECT_ID || null,
    warnings: []
  };
  if (!hadConnection) buildLog.warnings.push('No Firestore connection established');
  if (articles.length === 0) buildLog.warnings.push('Zero articles returned');
  writeIfChanged(path.join(docsDir, 'build_log.json'), JSON.stringify(buildLog, null, 4));

  // index.html (same shell as before)
  const indexHtml = `<!DOCTYPE html>
<html lang="en">
<head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>AI Articles</title>
        <link rel="stylesheet" href="css/main.css">
</head>
<body>
        <div id="header">
                <h1>AI Articles</h1>
        </div>
        <div id="articles">Loading articles...</div>
        <script src="js/main.js"></script>
</body>
</html>`;
  writeIfChanged(path.join(docsDir, 'index.html'), indexHtml);

  // Copy assets (css/js)
  function copyDir(src, dest) {
    if (!fs.existsSync(src)) return;
    if (!fs.existsSync(dest)) fs.mkdirSync(dest, {recursive: true});
    for (const entry of fs.readdirSync(src)) {
      const sp = path.join(src, entry);
      const dp = path.join(dest, entry);
      const stat = fs.statSync(sp);
      if (stat.isDirectory()) copyDir(sp, dp); else fs.copyFileSync(sp, dp);
    }
  }
  copyDir(path.join(__dirname, '..', 'css'), path.join(docsDir, 'css'));
  copyDir(path.join(__dirname, '..', 'js'), path.join(docsDir, 'js'));
  log('INFO', 'Assets copied.');

  if (!hadConnection) {
    log('ERROR', 'Build failed: No Firestore connection.');
    process.exit(1);
  }
  if (articles.length === 0 && process.env.BUILD_ALLOW_EMPTY !== '1') {
    log('ERROR', 'Build failed: Zero articles (set BUILD_ALLOW_EMPTY=1 to allow).');
    process.exit(2);
  }
  log('INFO', 'Build completed successfully.');
}

main().catch(err => { log('ERROR', err.stack || err.message); process.exit(1); });
