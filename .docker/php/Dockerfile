FROM php:7.4-apache

# setup additional tooling
RUN apt update
RUN apt install -y default-mysql-client imagemagick libpng-dev libzip-dev wget zip unzip

# setup mail plugins
RUN pear install Mail Mail_Mime Auth_SASL pear/Net_SMTP

# install composer
RUN php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');" && \
#    php -r "if (hash_file('sha384', 'composer-setup.php') === 'e0012edf3e80b6978849f5eff0d4b4e4c79ff1609dd1e613307e16318854d24ae64f26d17af3ef0bf7cfb710ca74755a') { echo 'Installer verified'; } else { echo 'Installer corrupt'; unlink('composer-setup.php'); } echo PHP_EOL;" && \
    php composer-setup.php --install-dir=/usr/local/bin --filename=composer && \
    php -r "unlink('composer-setup.php');"
COPY .docker/php/composer.json /var/www/

# configure apache
RUN rm -r /var/www/html
COPY .docker/php/000-default.conf /etc/apache2/sites-enabled/000-default.conf
COPY .docker/php/default-ssl.conf /etc/apache2/sites-enabled/default-ssl.conf
COPY .docker/php/uploads.ini /usr/local/etc/php/conf.d/uploads.ini
RUN a2enmod rewrite expires speling ssl
RUN docker-php-ext-configure zip --with-libzip
RUN docker-php-ext-install mbstring pdo pdo_mysql mysqli zip gd

# copy over files
COPY bin /var/www/bin
COPY public /var/www/public
COPY src /var/www/src
COPY templates /var/www/templates

# compress our css and js
# TODO

# install plugins
ARG MPDF_VERSION=7.1.9
ENV MPDF_VERSION="${MPDF_VERSION}"

RUN mkdir /var/www/resources
## install jSignature plugin
RUN cd /var/www/public/js && \
        mkdir jSignature && \
        cd jSignature && \
        wget --quiet http://willowsystems.github.io/jSignature/jSignature.zip && \
        unzip -qq jSignature.zip && \
        rm jSignature.zip
## install mPDF
RUN cd /var/www/ && \
        composer require mpdf/mpdf:${MPDF_VERSION}
## install/configure ua parser plugin
RUN cd /var/www/src && \
        wget --quiet https://raw.githubusercontent.com/cbschuld/Browser.php/master/src/Browser.php
## install browscap ini
RUN cd /var/www/resources && \
        wget --quiet http://browscap.org/stream?q=PHP_BrowsCapINI && \
        mv stream\?q\=PHP_BrowsCapINI php_browscap.ini

# try setting the timezone
ARG TZ=America/New_York
ENV TZ=$TZ
RUN ln -snf /usr/share/zoneinfo/$TZ /etc/localtime && echo $TZ > /etc/timezone

# setup our volumes
VOLUME /var/www/content
VOLUME /var/www/logs

RUN ln -s /var/www/content/commercial /var/www/public/commercial/img
RUN ln -s /var/www/content/portrait /var/www/public/portrait/img
RUN ln -s /var/www/content/wedding /var/www/public/wedding/img
RUN ln -s /var/www/content/b-nai-mitzvah /var/www/public/b-nai-mitzvah/img
RUN ln -s /var/www/content/main /var/www/public/img/main
RUN ln -s /var/www/content/reviews /var/www/public/img/reviews
RUN ln -s /var/www/content/albums /var/www/public/albums
RUN ln -s /var/www/content/blog /var/www/public/blog/posts
RUN ln -s /var/www/content/contracts /var/www/public/user/contracts
RUN ln -s /var/www/src/errors /var/www/public/errors
RUN chown -R www-data:www-data /var/www/

WORKDIR /var/www

CMD ["./bin/setup-database.sh"]