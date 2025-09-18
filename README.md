# Newsletter Automation (Pure Firestore Client Version)

This simplified version loads articles directly from Firestore in the browser. No server-side build or static JSON generation is required. The only moving part is a single page (`index.html`) plus `js/main.js` which fetches and renders documents from the `articles` collection.

## How It Works
1. `index.html` defines `window.FIREBASE_CONFIG` (public web config from your Firebase console) and optional `window.ARTICLE_LIMIT`.
2. `js/main.js` (ES module) initializes Firebase App + Firestore using the modular v9 SDK delivered via CDN.
3. It queries `articles` ordered by `date` desc (and `id` desc if that secondary field exists) with a limit.
4. Results are normalized and rendered immediately into the DOM.

## Firestore Data Model
Collection: `articles`
Recommended document fields:
```
id         (number|string)  // optional; falls back to doc.id
title      (string)
text_body  (string)
sources    (array|string)   // array of URLs or delimited string
date       (Timestamp|string)
```
`date` Firestore Timestamps are converted to `YYYY-MM-DD`.

## Example index.html Config Snippet
Already included at the top of `index.html`:
```
<script>
window.FIREBASE_CONFIG = {
  apiKey: "YOUR_API_KEY",
  authDomain: "your-project.firebaseapp.com",
  projectId: "your-project",
  appId: "YOUR_APP_ID"
};
window.ARTICLE_LIMIT = 10; // optional
</script>
```
Replace values with your real Firebase web app configuration (public values – not secret).

## Security Rules (Public Read Example)
Only use public read if data is non-sensitive:
```
rules_version = '2';
service cloud.firestore {
  match /databases/{database}/documents {
    match /articles/{docId} {
      allow read; // PUBLIC READ (be cautious)
    }
  }
}
```
For restricted access, add auth (e.g. anonymous + rules, or custom claims) – not covered here.

## Rendering Features
- `**bold**` → `<strong>`
- `%%` → `'` apostrophe
- Newlines → `<br>`
- Sources parsed from array, JSON array string, newline, comma, or pipe-delimited formats
- Duplicate + invalid URLs removed

## File Overview
```
index.html        # Root page with Firebase config + script include
js/main.js        # Firestore query + rendering logic (ES module)
css/main.css      # Styles reused from earlier version
```

## Deployment
Because everything is client-side, you can host via GitHub Pages (root) or any static host. No build step necessary.

## Optional Enhancements
- Real-time updates: switch one-time `getDocs` to `onSnapshot`.
- Pagination / infinite scroll using query cursors.
- Full-text search via an external index (Algolia / Meilisearch) or local mini-search.
- Offline caching (Service Worker) for articles.

## Switching Back to Static Build (If Needed)
If you later want deterministic versioned snapshots (e.g., immutable archives), reintroduce a Node build script that queries Firestore with `firebase-admin` and writes `articles.json`. The current layout won’t conflict.

---
Minimal Firestore client setup complete. Add your Firebase config and publish.
