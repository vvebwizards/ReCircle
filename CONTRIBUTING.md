Contributing to Waste2Product

## Branch & Release Flow

We use a simplified Git branching model:

1. `main` – Always production-ready. Only updated via PR from a release branch.
2. `develop` – Integrated work for the next release. Features merge here first.
3. `release/x.y.z` – Temporary hardening branch (bug fixes, version bump, changelog) before merging to `main`.
4. `feat/<short-name>` – Individual feature branches (or `fix/<short-name>` for bug fixes).

Lifecycle:
1. Open an Issue describing the feature or bug (include scope & acceptance criteria).
2. Create a feature branch from `develop`:
	```bash
	git checkout develop
	git pull
	git checkout -b feat/short-name
	```
3. Commit small, logical changes (reference the issue: `#123`).
4. Open a PR into `develop` when done. Get review, address feedback, squash if needed.
5. When `develop` is stable and ready to cut a release:
	```bash
	git checkout develop
	git pull
	git checkout -b release/x.y.z
	```
	- Update version info (if applicable).
	- Update CHANGELOG (if added later).
	- Only allow bug fixes & docs tweaks here.
6. Merge `release/x.y.z` into `main` via PR (use a descriptive title: `Release x.y.z`). Tag the commit.
7. Merge the release branch back into `develop` to keep version alignment, then delete the release branch.

Example PR flow:
```
feat branch -> PR to develop -> (multiple features accumulate) -> release/x.y.z -> PR to main -> tag -> merge back to develop
```

## Naming Conventions
Branches:
- `feat/dashboard-filters`
- `fix/login-redirect`
- `chore/ci-upgrade`

Commits (prefer conventional style):
- `feat: add admin sidebar metrics`
- `fix: correct CO2 stat formatting`
- `chore: bump vite version`
- `docs: expand setup guide`

## Pull Request Checklist
- Linked Issue (e.g. Closes #123)
- Screenshots / GIF (UI changes)
- Tests added/updated (if logic added)
- No debug dumps / commented code
- Passes test suite: `php artisan test`
- PHP formatted with Pint: `vendor/bin/pint` (pre-commit runs Pint `--test`)
- Static analysis clean: `composer run phpstan`
- JS lint clean: `npm run lint`
- Build passes: `npm run build` (if asset changes)

## Code Guidelines
- Follow PSR-12 for PHP.
- Run Pint before pushing:
	```bash
	vendor/bin/pint
	```
- Keep migrations & seeders minimal; one concern per file.
- Avoid over-coupling controllers – push logic into services/helpers where it grows.
- Front-end JS lives in `resources/js/` and should avoid inline scripts where possible.

### Pre-commit hooks
We use Husky to run quality gates locally:
- Laravel Pint (style) with `--test`
- PHPStan (static analysis)
- ESLint (JS)

Skip in emergencies by setting `SKIP_HOOKS=1` before the commit command. Ensure you run the tools manually afterward.

### CI overview
GitHub Actions runs:
- php-tests (SQLite, clears caches, `php artisan test`, Clover coverage for SonarCloud)
- frontend-build (Node 20, `npm ci && npm run build`)
- pint (Laravel Pint check)
- phpstan (Larastan analysis)
- js-lint (ESLint flat config)
- sonarcloud (SonarCloud analysis) — requires `SONAR_TOKEN` repository secret

### SonarCloud
- Project config: `sonar-project.properties`.
- Coverage: PHPUnit Clover at `coverage-reports/phpunit-coverage.xml` produced by CI.
- JS coverage is currently excluded until a JS test harness is added.

## Testing
Run tests locally:
```bash
php artisan test
```
Add at least one happy path + an edge case when introducing new service-level logic.

## Issues
Before starting work:
1. Search existing issues.
2. If new: create one with context, problem, proposed approach.
3. Assign yourself (or request assignment) before opening a feature branch.

## Docs & Setup
If your change affects onboarding or environment steps, update `docs/setup.md` and mention it in your PR.

## Support
For environment/setup friction, document the resolution and link the commit in the PR description.

## Release Tags
Semantic versioning: `MAJOR.MINOR.PATCH`.
```bash
git tag -a v1.2.0 -m "Release 1.2.0"
git push origin v1.2.0
```

---
Thanks for contributing! Keep commits focused and PRs small where possible.

