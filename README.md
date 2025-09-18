# Newsletter Automation (Static GitHub Pages Version).

This repository now operates in **static-only** mode. A GitHub Actions workflow connects to your MySQL database (filess.io) and generates a static site inside `docs/`. GitHub Pages serves the contents of `docs/` directly—no PHP runtime is required.

## How It Works
1. Workflow runs (`.github/workflows/build-static.yml`).
2. PHP script `scripts/build_articles.php` connects to MySQL using secrets.
3. It queries the `articles` table and writes:
   - `docs/articles.json` (data)
   - `docs/index.html` (page shell)
   - Copies `css/` + `js/` into `docs/`.
4. `js/main.js` fetches `articles.json` and renders the list client-side.

## Database Table Schema
Your MySQL `articles` table must contain:
```
ID (INT PRIMARY KEY AUTO_INCREMENT)
title (VARCHAR)        -- mapped to frontend field `title`
text_body (TEXT)
sources (TEXT or VARCHAR)
date (DATETIME or DATE)
```

## Required GitHub Action Secrets
Add these in: Settings → Secrets and variables → Actions → New repository secret.
```
MYSQL_HOST   (example: sql123.filess.io)
MYSQL_DB     (example: newsletter_prod)
MYSQL_USER   (example: newsletter_user)
MYSQL_PASS   (your password)
MYSQL_PORT   (optional, default 3306)
```

## Enable GitHub Pages
1. Settings → Pages
2. Source: Deploy from a branch
3. Branch: `main` / Folder: `/docs`
4. Save and wait for deployment.

## Triggering a Build
- Push a commit (any file)
- Or: Actions tab → select workflow → Run workflow

## Adding / Updating Articles
Insert or update rows in the MySQL `articles` table. The next workflow run refreshes `articles.json`.

## Error Handling & Logs
- If connection fails: workflow logs will show messages like `MySQL connection failed`.
- `articles.json` may end up empty if query returns no rows or fails.

## Customization Ideas
- Pagination: generate page segments (`articles-page-1.json`, etc.).
- RSS feed: emit `docs/feed.xml` inside build script.
- Search: create a prebuilt index (e.g. `search-index.json`).

## File Map (Static Mode)
```
scripts/build_articles.php  # Build script
docs/index.html             # Generated static entry
docs/articles.json          # Generated dataset
docs/css/                   # Copied styles
docs/js/                    # Copied scripts
js/main.js                  # Rendering logic (source)
css/main.css                # Styles (source)
.github/workflows/build-static.yml  # Automation
```

Legacy dynamic PHP files have been deprecated and stripped.

## Security Notes
- Never commit database credentials.
- Only expose publicly safe columns; remove or rename sensitive fields before output.

## License
Add a LICENSE file if distributing.

---
Static build pipeline is ready. Add secrets, enable Pages, and you’re live.
