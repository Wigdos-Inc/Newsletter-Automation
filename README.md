# Newsletter Automation (Client‑Only Firestore)

Minimal static site. On load, `js/main.js` reads formatted article documents from Firestore and injects them into the page.

## Quick Start
1. Get Firebase Web config (Console → Project Settings → Your apps) and paste into `index.html` where `window.FIREBASE_CONFIG` is defined.
2. Create a Firestore collection `articles` with documents containing:
```
title (string)
text_body (string)  // or textBody
sources (array|string) optional
date (Timestamp|string) optional
id (number|string) optional
```
3. (Optional) Create doc `newsletters/news` with field `test` to see the test block.
4. Set rules for public read-only (if data is non-sensitive):
```
rules_version = '2';
service cloud.firestore {
  match /databases/{database}/documents {
    match /articles/{docId} { allow read; allow write: if false; }
    match /newsletters/{docId} { allow read; allow write: if false; }
    match /{document=**} { allow read, write: if false; }
  }
}
```
5. Open `index.html` via any static web host (GitHub Pages, Netlify, etc.).

## Formatting Applied
* `**bold**` → `<strong>`
* `%%` → `'`
* Newlines → `<br>`
* `sources` parsed from multiple input styles; invalid/duplicate URLs dropped.

## Optional Enhancements
Real-time updates (use `onSnapshot`), pagination, search, offline caching—add later if needed.

That’s it.
