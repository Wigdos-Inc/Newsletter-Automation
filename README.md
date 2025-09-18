# Newsletter Automation (Unified Build + Jekyll Deploy)

The site is generated and deployed by a **single GitHub Actions workflow** that:
1. Runs the PHP build script to pull the latest MySQL rows.
2. Regenerates `docs/articles.json`, `docs/index.html`, and asset copies.
3. Optionally commits changes (only if content changed).
4. Builds & deploys the `docs/` directory with Jekyll to GitHub Pages.

You can trigger it by:
- Pushing changes (scripts / css / js / workflow itself)
- Manual run (workflow_dispatch) with an optional `article_limit` input
- External webhook (`repository_dispatch` event_type: `deploy-site`)

## Database Table Schema
Your MySQL `articles` table must contain:
```
ID (INT PRIMARY KEY AUTO_INCREMENT)
title (VARCHAR)        -- mapped to frontend `title`
text_body (TEXT)
sources (TEXT or VARCHAR)
date (DATETIME or DATE)
```
`ARTICLE_LIMIT` (default 5) can be overridden via manual dispatch input or repository_dispatch payload.

## Required GitHub Action Secrets
```
MYSQL_HOST
MYSQL_DB
MYSQL_USER
MYSQL_PASS
MYSQL_PORT   (optional, default 3306)
```
Optional: `BUILD_ALLOW_EMPTY=1` if you want deployment even when zero rows.

## Workflow
```
.github/workflows/jekyll-gh-pages.yml   # Unified build + deploy
```
Key steps inside the workflow:
- Setup PHP → run `scripts/build_articles.php`
- Commit changes in `docs/` if diffs exist
- Jekyll build (`source: docs`) → upload artifact → deploy

### Override ARTICLE_LIMIT Examples
Manual dispatch (GitHub UI): provide input `article_limit=15`.
Repository dispatch payload:
```
POST /repos/<owner>/<repo>/dispatches
{
  "event_type": "deploy-site",
  "client_payload": { "article_limit": 25 }
}
```

## Key Files
```
scripts/build_articles.php  # Pulls DB rows & writes static assets
/docs/index.html            # Generated HTML shell
/docs/articles.json         # Dataset consumed by JS
/docs/css/ & /docs/js/      # Deployed assets
/js/main.js                 # Rendering logic
/css/main.css               # Styles
/docs/_config.yml           # Jekyll config (docs as root)
```

## Frontend Rendering Notes
- `**bold**` → `<strong>`
- `%%` → `'` apostrophe
- Newlines → `<br>`
- Sources auto-parse JSON array, newline, comma, or pipe delimiters
- Cache busting added: `articles.json?cb=<timestamp>`

## Webhook Trigger (repository_dispatch)
Endpoint:
```
POST https://api.github.com/repos/Wigdos-Inc/Newsletter-Automation/dispatches
Headers: Authorization: Bearer <TOKEN>, Accept: application/vnd.github+json
Body:
{
  "event_type": "deploy-site",
  "client_payload": { "reason": "external refresh", "article_limit": 12 }
}
```

## Customization Ideas
- Pagination / multiple JSON pages
- SEO & sitemap (add `jekyll-seo-tag`, `jekyll-sitemap` if needed)
- Search index file
- Feed (RSS/Atom) generation in PHP

## Security
Never commit credentials; secrets are injected at runtime.

---
Unified pipeline ready. Trigger manually, by push, or externally via `repository_dispatch`.
