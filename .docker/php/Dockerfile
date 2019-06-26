FROM php:7.3.0-apache

# configure apache
RUN rm -r /var/www/html
COPY .docker/php/vhost.conf /etc/apache2/sites-available/000-default.conf
COPY .docker/php/uploads.ini /usr/local/etc/php/conf.d/uploads.ini
RUN a2enmod rewrite
RUN docker-php-ext-install mbstring pdo pdo_mysql mysqli

# setup additional tooling
RUN apt-get update && \
        apt-get install -yy mysql-client && \
        apt-get install -yy imagemagick

# copy over files
COPY bin /var/www/bin
COPY public /var/www/public
COPY resources /var/www/resources
COPY src /var/www/src
COPY templates /var/www/templates

VOLUME /var/www/content
VOLUME /var/www/logs

RUN ln -s /var/www/content/commercial /var/www/public/commercial/img
RUN ln -s /var/www/content/portrait /var/www/public/portrait/img
RUN ln -s /var/www/content/wedding /var/www/public/wedding/img
RUN ln -s /var/www/content/main /var/www/public/img/main
RUN ln -s /var/www/content/retouch /var/www/public/retouch
RUN ln -s /var/www/content/reviews /var/www/public/img/reviews
RUN ln -s /var/www/content/albums /var/www/public/albums
RUN ln -s /var/www/content/blog /var/www/public/blog/posts
RUN ln -s /var/www/content/contracts /var/www/public/user/contracts
RUN ln -s /var/www/src/errors /var/www/public/errors
RUN chown -R www-data:www-data /var/www/

WORKDIR /var/www

CMD ["./bin/setup-database.sh"]