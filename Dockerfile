FROM php:7.3-fpm-alpine

WORKDIR /var/www

RUN chown -R www-data:www-data /var/www

CMD ["php-fpm"]