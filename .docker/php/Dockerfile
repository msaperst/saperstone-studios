FROM php:7.3.0-apache

# configure apache
RUN rm -r /var/www/html
COPY .docker/php/vhost.conf /etc/apache2/sites-available/000-default.conf
RUN a2enmod rewrite
RUN docker-php-ext-install mbstring pdo pdo_mysql mysqli

# setup additional tooling
RUN apt-get update && \
        apt-get install -yy mysql-client
#        apt-get install -yy php-imagick imagemagick

# copy over files
COPY bin /var/www/bin
COPY public /var/www/public
COPY resources /var/www/resources
COPY src /var/www/src
COPY templates /var/www/templates
RUN ln -s /var/www/src/errors /var/www/public/errors
RUN chown -R www-data:www-data /var/www/

WORKDIR /var/www

CMD ["./bin/setup-database.sh"]