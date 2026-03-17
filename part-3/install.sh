# only create file if it doesn't exist
[ ! -f ./database/database.sqlite ] && touch ./database/database.sqlite
cp .env.example .env
composer update
php artisan migrate:fresh --seed
php artisan serve
