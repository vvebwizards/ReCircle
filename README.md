# Waste2Product

[![Quality Gate Status](https://sonarcloud.io/api/project_badges/measure?project=vvebwizards_Waste2Product&metric=alert_status)](https://sonarcloud.io/summary/new_code?id=vvebwizards_Waste2Product)
[![Reliability Rating](https://sonarcloud.io/api/project_badges/measure?project=vvebwizards_Waste2Product&metric=reliability_rating)](https://sonarcloud.io/summary/new_code?id=vvebwizards_Waste2Product)
[![Security Rating](https://sonarcloud.io/api/project_badges/measure?project=vvebwizards_Waste2Product&metric=security_rating)](https://sonarcloud.io/summary/new_code?id=vvebwizards_Waste2Product)
[![Technical Debt](https://sonarcloud.io/api/project_badges/measure?project=vvebwizards_Waste2Product&metric=sqale_index)](https://sonarcloud.io/summary/new_code?id=vvebwizards_Waste2Product)
[![Code Smells](https://sonarcloud.io/api/project_badges/measure?project=vvebwizards_Waste2Product&metric=code_smells)](https://sonarcloud.io/summary/new_code?id=vvebwizards_Waste2Product)

Sustainable waste-to-product marketplace prototype built with Laravel + Vite.

## 1. Prerequisites

Install / have on PATH:

| Tool | Recommended |
|------|-------------|
| PHP  | 8.3+ (CLI) |
| Composer | latest |
| Node.js | 20+ |
| npm | 10+ |
| Git | latest |
| (Optional) MySQL / XAMPP | For MySQL instead of SQLite |

> Quick demo can run with the included SQLite database (default config) – fastest path.

## 2. Clone & Install

```powershell
git clone <your-fork-or-repo-url> Waste2Product
cd Waste2Product
composer install
npm install
```

## 3. Database Options

### Option A: SQLite (fastest)
Already configured in `config/database.php` (points at `database/database.sqlite`). If the file is missing:
```powershell
ni database\database.sqlite -ItemType File
```
Run migrations:
```powershell
php artisan migrate
```

### Option B: MySQL (XAMPP / local server)
1. Start MySQL (XAMPP Control Panel: Start MySQL).
2. Create a database (e.g. `waste2product`) OR run:
   ```powershell
   php scripts/create_mysql_db.php
   ```
3. Migrate:
   ```powershell
   php artisan migrate
   ```

Or run the helper (attempts dependencies + migrate):
```powershell
./scripts/setup.ps1
```

## 4. Run Dev Servers

Terminal 1 (Laravel):
```powershell
php artisan serve --host=127.0.0.1 --port=8000
```
Terminal 2 (Vite with HMR):
```powershell
npm run dev
```
Open: http://127.0.0.1:8000

## 5. Build for Production
```powershell
npm run build
php artisan config:cache route:cache view:cache
```
Serve via a real web server (Nginx/Apache) pointing document root at `public/`.

## 6. Continuous Integration (CI)
The GitHub Actions workflow runs only for pushes and pull requests targeting the `develop` branch (feature branches open PRs into `develop`).

Pipeline stages / jobs:
1. Stage 1 (build & test, run in parallel):
   - php-tests: provisions SQLite DB, clears caches, runs `php artisan test` with Clover coverage output for SonarCloud.
   - frontend-build: Node 20, caches npm, `npm ci && npm run build` to validate asset build.
2. Stage 2 (quality gates, all depend on both Stage 1 jobs and run in parallel):
   - pint: Laravel Pint style check (`--test`).
   - phpstan: Static analysis (Larastan) using `phpstan.neon.dist`.
   - js-lint: ESLint (flat config) over `resources/js` sources.
   - sonarcloud: Code + coverage analysis (public repo only, gated by secret `SONAR_TOKEN`).
3. Stage 3 (final):
   - docker-verify: Builds the multi-stage `Dockerfile` (no push) tagged `recircle:test` to ensure container reproducibility.

Key implementation notes:
- Matrix removed: single baseline runtime on PHP 8.2 keeps the pipeline fast.
- Test coverage path: `coverage-reports/phpunit-coverage.xml` (Clover) ingested by SonarCloud.
- Sonar configuration centralized in `sonar-project.properties` (no inline args) and only runs if the repository is public.
- Docker build uses Buildx with provenance disabled for speed; no image is pushed (verification only).
- Future enhancements (not yet implemented): production asset copy stage, opcache tuning, path-based workflow filters to skip Docker on docs-only changes.

Repository rename: Upstream moved to `ReCircle` organization naming; historical badges & keys (Sonar project `vvebwizards_Waste2Product`) retained for continuity.

Local expectations before pushing:
- Format PHP: `vendor/bin/pint` (or let pre-commit run it).
- Static analysis: `composer run phpstan`.
- JS lint: `npm run lint`.
- Tests: `php artisan test`.

Pre-commit hook
- Husky runs Pint, PHPStan, and ESLint. Bypass with `SKIP_HOOKS=1` if absolutely required.

Troubleshooting CI
- If Blade Vite assets break tests: we guard `@vite` in layouts to skip only in `testing` env.
- If coverage is empty, ensure Xdebug is enabled in CI (already configured) and the Clover path `coverage-reports/phpunit-coverage.xml` exists.

## 7. Demo Auth Flow (Front-end Only)
Front-end uses localStorage (`rc_user`, `rc_auth`) for a temporary fake auth:
1. `/auth` → sign in (stores `rc_user`).
2. `/twofa` → submit code (sets `rc_auth=true`).
3. `/dashboard` (user) or `/admin/dashboard` (admin demo).
4. Sign out clears keys.

Replace later with real Laravel auth (Breeze / Fortify / custom guards).

## 8. Key Routes
| Route | Purpose |
|-------|---------|
| `/` | Landing page |
| `/auth` | Sign in / Sign up demo |
| `/twofa` | Fake 2FA step |
| `/forgot-password` | Demo recovery flow |
| `/dashboard` | User dashboard demo |
| `/admin/dashboard` | Admin dashboard (pinned sidebar) |

## 9. Structure Highlights
| Path | Description |
|------|-------------|
| `resources/views/layouts/app.blade.php` | Public layout |
| `resources/views/layouts/admin.blade.php` | Admin layout |
| `resources/views/admin/partials/sidebar.blade.php` | Reusable sidebar |
| `resources/js/main.js` | Global UI behaviors |
| `resources/js/dashboard.js` | User dashboard logic |
| `resources/js/admin-dashboard.js` | Admin dashboard logic |
| `resources/css/style.css` | Template styling |

## 10. Contributing

Please read [CONTRIBUTING.md](CONTRIBUTING.md) for the branching model (feature -> develop -> release -> main), issue workflow, commit conventions, and PR checklist.

Before pushing or opening a PR, format PHP with Pint:

```powershell
vendor/bin/pint
```

---
Contributions & feedback welcome.
