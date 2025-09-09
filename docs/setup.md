# Windows + XAMPP setup notes

1. Install XAMPP and start Apache & MySQL from the XAMPP control panel.
2. Ensure your PHP CLI is the one you want (the project used PHP 8.4 in this workspace). If you want to use XAMPP's PHP, add `C:\xampp\php` to your PATH.
3. Ensure the `pdo_mysql` extension is enabled for the PHP CLI that runs `php artisan` (open `php -m` and look for `pdo_mysql`).
4. Edit `.env` to contain your MySQL credentials (DB_HOST, DB_PORT, DB_DATABASE, DB_USERNAME, DB_PASSWORD).
5. Run the project setup helper:

```powershell
.\scripts\setup.ps1
```

6. Start the dev server:

```powershell
php artisan serve --host=127.0.0.1 --port=8000
```

If you prefer phpMyAdmin, open http://localhost/phpmyadmin/ and create the database manually (name: `waste2product`).
