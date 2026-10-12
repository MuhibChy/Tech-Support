# GitHub Pages deployment — Tech-Support

> Note on placement: this file lives at the repository root (not under `docs/`)
> because `docs/` **is** the published Pages artifact. Markdown placed there would
> be served as public site content.

## Live site

- **URL:** https://muhibchy.github.io/Tech-Support/
- **Source:** `main` branch, `/docs` folder (legacy Pages build — see *Why legacy* below)
- **Status page:** https://github.com/MuhibChy/Tech-Support/deployments

## Diagnosis log (2026-10-12)

**Symptom:** the `Deploy to GitHub Pages` Actions workflow failed on every push
(`Exported 0 pages, 190 failed`, HTTP 500 on all routes); the live site served
only a stale static export.

**Root cause (verified by local reproduction):** the repository did not track
`storage/framework/{cache/data,sessions,testing,views}` or `storage/logs`.
On any fresh checkout (CI runners, new machines) those directories are absent,
so Laravel's Blade compiler receives `realpath() === false` and throws
`InvalidArgumentException: Please provide a valid cache path` — HTTP 500 on
every rendered page, including the test suite (`BladeCompileTest` failed for
the same reason). A secondary contributor was an incomplete PHP extension list
in CI (`openssl`, `tokenizer`, `fileinfo`, `curl`, `pdo_sqlite` missing).

**Fix applied:**
1. Committed standard Laravel storage skeleton placeholders
   (`storage/framework/*/.gitignore`, `storage/logs/.gitignore`); narrowed root
   `.gitignore` so only runtime files are ignored.
2. Added a `Prepare Storage Directories` step (mkdir + chmod + existence guard)
   and the full extension list to both `.github/workflows/ci.yml` and
   `.github/workflows/deploy-pages.yml`.

**Verified:** `npm ci` + `npm run build` succeed; homepage renders HTTP 200
locally; `php scripts/generate_docs.php` → **190 pages, 0 failed**;
`qa_docs.php` PASSED (titles/meta/canonical/alt/assets/sitemap);
`check_docs_links.php` PASSED (17,044 on-site refs, 0 broken, 0 backend-only
anchors); static output scanned clean for secrets and private emails.

## Architecture split (read this before expecting logins to work)

| Layer | Where | What works |
|---|---|---|
| Static marketing site | GitHub Pages (`/docs`) | All public pages, navigation, pricing, KB articles, contact info, sitemap, 404 |
| Laravel SaaS backend | **Not on Pages** — needs a PHP host | Login, tickets, portal, payments, APIs, database |

Login/register pages exist statically **only as honest previews**: they carry a
visible notice that the static mirror cannot submit forms, and every backend
link was rewritten to the live application URL (verified: zero backend-only
anchors remain). No fake success states anywhere.

## Build & publish process

1. Push to `main` triggers `Deploy to GitHub Pages` (`workflow_dispatch` also available).
2. `npm ci` → `npm run build` (Vite → `public/build`, git-ignored).
3. Storage dirs prepared; `.env` created from example; SQLite touched; key generated.
4. `migrate --force` → `db:seed --force`.
5. `php scripts/generate_docs.php` renders every public GET route into `docs/`
   with the `/Tech-Support` base path baked in, copies build assets + `og-cover.jpg`,
   injects static-form notices, writes `sitemap.xml`/`robots.txt`/`404.html`/`.nojekyll`.
6. `Verify Static Export` asserts `index.html`, `404.html`, `robots.txt`,
   `sitemap.xml`, `.nojekyll` and key section indexes exist.
7. `upload-pages-artifact` (`./docs`) → `deploy-pages` → live in ~1 minute.

Concurrency group `pages` with `cancel-in-progress: false` prevents overlapping
deploys; least-privilege `contents: read, pages: write, id-token: write`.

## Base path & asset rules

- Project site → all generated URLs are absolute under `/Tech-Support/`.
  Never change `APP_URL`/asset config for Pages without also re-running the export.
- `docs/build/` is **committed generated output** (required — Pages has no build step
  in legacy mode); `public/build/` stays git-ignored.
- `docs/.nojekyll` must remain — it stops Jekyll from mangling `_`-prefixed asset dirs.
- Filenames are case-sensitive on Pages; the link checker runs on every export.

## Troubleshooting

| Symptom | Cause → fix |
|---|---|
| Workflow fails at export with HTTP 500s | Storage skeleton missing (fixed 2026-10-12); re-check the Prepare step |
| Unstyled pages / 404 on CSS-JS | `npm run build` failed or `docs/build` stale → rebuild via workflow |
| Old content live after push | Legacy Pages build takes ~1 min; check Deployments tab, hard-refresh |
| Form does nothing on live site | By design — static mirror; use the Laravel app URL |
| `test ! -f docs/index.html` fails | Export crashed — read the per-route FAILED lines in the job log |

## Backend deployment (full SaaS)

GitHub Pages cannot run PHP. To operate logins, tickets, payments and APIs,
deploy the Laravel tree to a PHP 8.0+ host with MySQL, TLS, storage, workers
and scheduler per **`PRODUCTION_DEPLOYMENT.md`** (unchanged). Required secrets
(`APP_KEY`, DB, SMTP, payment keys) live in host env config — never in git.
