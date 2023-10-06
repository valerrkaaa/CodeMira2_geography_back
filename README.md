Команда Уфимского Университета Науки и Технологий - Units:

Развёртывание: 
-Создать файл .env на основе .env.example
-Создать файл config/database.php на основе database.php.example и для pgsql заполнить поля
-В терминале прописать:
composer update
php artisan key:generate
php artisan jwt:secret
php artisan migrate
-php artisan serve
или:
-php artisan serve --host=127.0.0.1 --port=3001
