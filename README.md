### Hướng dẫn cài đặt

Clone project về
```
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
```

Xóa trắng dữ liệu và khởi tạo lại
```
php artisan migrate:fresh --seed
```
