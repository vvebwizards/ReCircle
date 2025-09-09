<#
scripts/setup.ps1

Purpose: idempotent Windows PowerShell helper to prepare the local development environment
for Waste2Product. It attempts to:
 - ensure composer dependencies are installed
 - copy .env.example -> .env when missing
 - create the MySQL database using scripts/create_mysql_db.php
 - generate an APP_KEY if missing
 - run migrations

Usage (PowerShell):
	.\scripts\setup.ps1

Notes:
 - Run from project root.
 - Ensure PHP CLI is available and has pdo_mysql enabled.
 - For XAMPP, consider adding C:\xampp\php to your PATH so the same PHP binary is used.
#>

Set-StrictMode -Version Latest

Write-Host "== Waste2Product setup helper =="

# Composer
if (-not (Test-Path vendor)) {
	Write-Host "Installing PHP dependencies (composer install)..."
	composer install
} else {
	Write-Host "Composer dependencies appear present (vendor/)."
}

# .env
if (-not (Test-Path .env)) {
	if (Test-Path .env.example) {
		Write-Host "Creating .env from .env.example"
		Copy-Item .env.example .env
	} else {
		Write-Warning ".env.example not found. Create .env manually."
	}
} else {
	Write-Host ".env already exists."
}

# Create MySQL DB
if (Test-Path .\scripts\create_mysql_db.php) {
	Write-Host "Attempting to create MySQL database using scripts/create_mysql_db.php"
	php .\scripts\create_mysql_db.php
} else {
	Write-Warning "scripts/create_mysql_db.php missing — create DB manually or restore the script."
}

# APP_KEY
$envContent = Get-Content .env -ErrorAction SilentlyContinue -Raw
if (-not $envContent) { $envContent = "" }
if ($envContent -notmatch "^APP_KEY=.+") {
	Write-Host "Generating APP_KEY..."
	php artisan key:generate
} else {
	Write-Host "APP_KEY already set in .env"
}

# Migrate
Write-Host "Running migrations (php artisan migrate --force)"
php artisan migrate --force

Write-Host "Setup completed. Start the dev server with: php artisan serve --host=127.0.0.1 --port=8000"

