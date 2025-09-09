Developer checklist

After pulling the repo:

1. Install dependencies
	composer install
	npm install (if you plan to work on frontend assets)
2. Copy env
	Copy .env.example .env
3. Run setup helper
	.\scripts\setup.ps1
4. Start server
	php artisan serve --host=127.0.0.1 --port=8000
5. Run tests
	./vendor/bin/phpunit

Common tasks
- Refresh migrations: php artisan migrate:fresh --seed
- Generate key: php artisan key:generate
- Clear caches: php artisan config:clear; php artisan cache:clear; php artisan route:clear

