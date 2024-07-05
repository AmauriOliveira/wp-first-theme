FROM wordpress:6.5.3-php8.1-apache

WORKDIR /var/www/html

COPY ./wp-content/ ./wp-content/
RUN chmod -R 755 ./wp-content/
