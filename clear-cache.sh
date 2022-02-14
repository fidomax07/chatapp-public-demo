#!/usr/bin/env bash

php artisan clear-compiled
php artisan cache:clear
php artisan config:clear
#php artisan debugbar:clear
php artisan event:clear
php artisan optimize:clear
#php artisan queue:clear
php artisan route:clear
composer dump-autoload
