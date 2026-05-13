FROM php:8.2-apache

RUN docker-php-ext-install pdo pdo_mysql mysqli

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

COPY . .

RUN composer install --no-dev --optimize-autoloader \
    && mkdir -p storage/sessions public/uploads/items \
    && chown -R www-data:www-data storage public/uploads

RUN a2enmod rewrite

COPY docker/000-default.conf /etc/apache2/sites-available/000-default.conf
COPY docker/start-apache.sh /usr/local/bin/start-apache
RUN chmod +x /usr/local/bin/start-apache

CMD ["start-apache"]
