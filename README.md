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

## 6. Real-time Features

### Real-time Bidding System
The application features a WebSocket-based real-time bidding system. When users place bids, they appear instantly for other users viewing the same listing, and bid cards on the dashboard update in real-time.

See [Real-time Bidding Documentation](docs/realtime-bids.md) for setup and usage details.

## 7. Continuous Integration (CI)
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

## 7. API Authentication (JWT)
Implemented custom stateless auth using JSON Web Tokens (firebase/php-jwt) with HttpOnly cookie storage.

### Endpoints
| Method | Path | Description | Auth Required |
|--------|------|-------------|---------------|
| POST | `/api/auth/login` | Authenticate user and set JWT cookie | No |
| POST | `/api/auth/refresh` | Refresh (rotate) token before expiry | Yes (valid cookie) |
| GET  | `/api/auth/me` | Return current user (id, name, email) | Yes |
| POST | `/api/auth/logout` | Clear JWT cookie | Yes (cookie optional for idempotency) |

### Login Request
```json
POST /api/auth/login
{
   "email": "test@example.com",
   "password": "password"
}
```

### Login Response
```json
{
   "token_type": "Bearer",
   "expires_at": "2025-09-16T13:10:25+00:00"
}
```
Token itself is stored only in an `HttpOnly` cookie named `access_token` (configurable via `config/jwt.php`). Not accessible from JS (`document.cookie`).

### Fetch Current User
```bash
curl -H "Accept: application/json" -b "access_token=<copied-if-non-HttpOnly>" http://localhost:8000/api/auth/me
```
Response:
```json
{
   "data": { "id": 1, "name": "Test User", "email": "test@example.com" }
}
```

### Refresh Token
Call periodically (e.g. 5 minutes before `expires_at`):
```bash
curl -X POST -H "Accept: application/json" -b cookies.txt -c cookies.txt http://localhost:8000/api/auth/refresh
```
Returns same shape as login with a new expiry and rotated cookie.

### Logout
```bash
curl -X POST -H "Accept: application/json" -H "X-CSRF-TOKEN: <csrf>" -b cookies.txt -c cookies.txt http://localhost:8000/api/auth/logout
```
Response:
```json
{ "message": "Logged out" }
```

### Front-end Integration Notes
1. After page load, JS attempts `/api/auth/me`; if 200 it builds authenticated nav, else guest nav.
2. Login form posts to `/api/auth/login`; upon success it calls `/api/auth/me` to hydrate user state then redirects.
3. Logout sends POST `/api/auth/logout` including `X-CSRF-TOKEN` (meta tag added to layouts) and then redirects to `/auth`.
4. All fetch calls include `credentials: 'include'` to send the cookie.

### Security Choices
| Aspect | Decision | Rationale |
|--------|----------|-----------|
| Storage | HttpOnly cookie | Prevent XSS token theft |
| SameSite | Lax | Allows normal navigation, reduces CSRF risk |
| Secure flag | Only in production | Local dev over HTTP still works |
| TTL | 60 minutes (configurable) | Reasonable session window |
| Refresh | Rotation endpoint | Prepares for silent refresh logic |
| Claims | iss, sub, iat, exp | Minimal set for validation |

### Two-Factor Authentication (2FA)
We support TOTP-based 2FA with QR provisioning and one-time recovery codes.

Endpoints (require a valid JWT unless noted):

| Method | Path                  | Description |
|--------|-----------------------|-------------|
| GET    | `/api/auth/2fa/setup`   | Returns otpauth URI, QR SVG, and recovery codes; creates a secret/codes if missing |
| POST   | `/api/auth/2fa/enable`  | Enable 2FA by verifying a 6-digit code from your authenticator app |
| POST   | `/api/auth/2fa/disable` | Disable 2FA and clear secret and recovery codes |

Login flow: If a user has 2FA enabled, `/api/auth/login` will return `403` with `{ "requires_twofa": true }` until a correct `twofa_code` or `recovery_code` is provided.

UI: Visit `/settings/security` while signed in to set up or disable 2FA, scan the QR, and copy/download recovery codes.

Security notes:
- Recovery codes are one-time use; using a code immediately removes it from the list.
- QR is rendered as SVG (no external image fetches).
- CSRF is required for POST enable/disable.

### Future Enhancements
- Silent refresh module in JS (schedule refresh before expiry)
- Separate refresh token & denylist for logout invalidation
- Role/permission claims or separate endpoint for authorization
- Rate limiting on `/api/auth/login` (add `throttle` middleware)

### Quick Dev cURL Examples
```bash
# Login (capture cookie)
curl -i -H "Accept: application/json" -H "X-CSRF-TOKEN: $(curl -s http://localhost:8000/auth | grep -oP 'meta name=\"csrf-token\" content=\"\K[^\"]+')" \
       -c cookies.txt -X POST -d '{"email":"test@example.com","password":"password"}' \
       -H "Content-Type: application/json" http://localhost:8000/api/auth/login

# Me
curl -H "Accept: application/json" -b cookies.txt http://localhost:8000/api/auth/me

# Refresh
curl -X POST -H "Accept: application/json" -b cookies.txt -c cookies.txt http://localhost:8000/api/auth/refresh

# Logout
CSRF=$(grep csrf-token: cookies.txt || echo "")
curl -X POST -H "Accept: application/json" -H "X-CSRF-TOKEN: $(curl -s http://localhost:8000/auth | grep -oP 'meta name=\"csrf-token\" content=\"\K[^\"]+')" -b cookies.txt -c cookies.txt http://localhost:8000/api/auth/logout
```

## 8. Key Routes
| Route | Purpose |
|-------|---------|
| `/` | Landing page |
| `/auth` | Auth page (sign in / sign up tabs) |
| `/twofa` | Placeholder 2FA step (demo) |
| `/settings/security` | Security settings (2FA setup/disable UI) |
| `/forgot-password` | Demo recovery flow |
| `/dashboard` | User dashboard |
| `/admin/dashboard` | Admin dashboard |

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
