FROM composer:latest as packages
WORKDIR /app
COPY ${PWD}/src /app/
RUN composer install --ignore-platform-reqs --no-scripts --no-interaction --prefer-dist --optimize-autoloader

FROM php:7.3-fpm-alpine as runtime
WORKDIR /var/www
COPY --from=packages --chown=www-data:www-data /app/ /var/www/
RUN chown -R www-data:www-data /var/www
CMD ["php-fpm"]