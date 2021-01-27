#!/bin/bash

#On error no such file entrypoint.sh, execute in terminal - dos2unix .docker\entrypoint.sh
cp ../../www/.env.example .env
cp ../../www/.env.testing.example .env.testing
composer install
php artisan key:generate
php artisan migrate

php-fpm
