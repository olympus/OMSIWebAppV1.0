@echo off
cd /d "%~dp0"
composer dump-autoload
php artisan optimize:clear
php artisan shield:generate --all