#!/bin/bash
docker-compose exec app php artisan key:generate
docker-compose exec app php artisan optimize
