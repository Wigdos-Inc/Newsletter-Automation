# Newsletter Automation (Jekyll + Scheduled Refresh)

The site is built from the `docs/` directory using GitHub Pages **Jekyll build with `source: docs`**. A scheduled + on‑demand refresh workflow pulls the latest rows from MySQL and regenerates static assets (`articles.json`, `index.html`, CSS/JS copies). Jekyll then deploys the folder as the site root.

## Current Pipeline
1. `Refresh Articles` (`.github/workflows/refresh.yml`) runs every 15 minutes (cron) or manually.
   - Executes `scripts/build_articles.php` (PHP 8.2) using MySQL secrets.
   - Rebuilds `docs/articles.json`, `docs/index.html`, and syncs `docs/css` + `docs/js`.
   - Commits only when there are content changes (avoids noisy deploys).
2. `Deploy Jekyll (docs as source)` (`.github/workflows/jekyll-gh-pages.yml`) triggers on pushes touching `docs/` and publishes the site via GitHub Pages.
3. Browser loads `index.html`; `js/main.js` fetches `articles.json?cb=<timestamp>` to avoid stale caching and renders entries.

## Database Table Schema
Your MySQL `articles` table must contain:
```
ID (INT PRIMARY KEY AUTO_INCREMENT)
title (VARCHAR)        -- mapped to frontend `title`
text_body (TEXT)
sources (TEXT or VARCHAR)
date (DATETIME or DATE)
```
Optional: set `ARTICLE_LIMIT` env (default 5) in the refresh workflow to control how many recent articles are emitted.

## Required GitHub Action Secrets
Add under: Settings → Secrets and variables → Actions.
```
MYSQL_HOST
MYSQL_DB
MYSQL_USER
MYSQL_PASS
MYSQL_PORT   (optional, default 3306)
```
You can set `BUILD_ALLOW_EMPTY=1` if you want an empty dataset to deploy without failing.

## GitHub Pages Configuration
Pages now uses the dedicated Pages deployment workflow (not branch/folder UI). Source is the artifact produced by Jekyll with `source: docs`. No further manual Pages configuration needed beyond enabling GitHub Pages for the repo once.

## Workflows Overview
```
.github/workflows/refresh.yml          # Generates content (every 15 min)
.github/workflows/jekyll-gh-pages.yml  # Builds & deploys Jekyll from docs/
```
(Removed: former `build-static.yml` which duplicated generation logic.)

## Key Files
```
scripts/build_articles.php  # Pulls DB rows and writes static assets
/docs/index.html            # Generated HTML shell
/docs/articles.json         # Data consumed by JS
/docs/css/ & /docs/js/      # Deployed assets
/js/main.js                 # Renders articles (source)
/css/main.css               # Styles (source)
/docs/_config.yml           # Jekyll config (docs as site root)
```

## Frontend Rendering Notes
- Bold syntax `**text**` in `text_body` becomes `<strong>text</strong>`.
- Custom placeholder `%%` becomes `'` (apostrophe).
- Newlines converted to `<br>`.
- Sources field auto-parses arrays, JSON strings, comma/pipe/newline delimited lists.

## Operational Notes
- Commits only occur when `articles.json` actually changes (minimizes needless deploys).
- Cache busting query param (`?cb=timestamp`) prevents CDN/browser stale JSON.
- If zero rows are returned the build exits with code 2 unless `BUILD_ALLOW_EMPTY=1`.

## Customization Ideas
- Pagination: adjust PHP to emit `articles-page-1.json`, etc.
- SEO: add `jekyll-seo-tag` (ensure whitelisted) in `docs/_config.yml` plus `<head>` tag update.
- Sitemap: add `jekyll-sitemap` plugin.
- RSS/Atom: generate `feed.xml` in the PHP build step.
- Search: precompute `search-index.json` with minimal tokens.

## Security
- Never echo raw secrets in logs.
- Restrict SQL to selected columns; sanitize any new columns before exposing.

## License
Add a `LICENSE` file if you intend to distribute.

---
Automated Jekyll + refresh pipeline is live. Adjust cron, limits, or add plugins as needed.
