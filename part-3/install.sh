touch ./database/database.sqlite
cp .env.example .env
composer install
php artisan migrate:fresh --seed
php artisan serve
