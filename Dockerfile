# Use the exact image from your Compose file
FROM php:8.2-apache

# 1. Install PDO MySQL extension (Required for Render & Local)
RUN docker-php-ext-install pdo pdo_mysql

# 2. Enable required Apache modules
RUN a2enmod rewrite ssl headers

# 3. Copy your custom Apache and PHP configurations
COPY ./apache/mensa.conf /etc/apache2/sites-available/mensa.conf
COPY ./apache/php.ini /usr/local/etc/php/conf.d/mensa-php.ini

# 4. Enable your site and disable the default
RUN a2ensite mensa.conf && a2dissite 000-default.conf

# 5. Copy your application code into the image (Crucial for Render)
COPY ./www /var/www/bbcmensa.com

# 6. Ensure correct permissions
RUN chown -R www-data:www-data /var/www/bbcmensa.com