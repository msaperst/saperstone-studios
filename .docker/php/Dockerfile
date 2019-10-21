FROM php:7.3.0-apache

# setup additional tooling
RUN apt-get update && \
        apt-get install -yy mysql-client && \
        apt-get install -yy imagemagick && \
        apt-get install -yy libzip-dev wget zip unzip
# setup mail plugins
RUN pear install Mail && \
        pear install Mail_Mime && \
        pear install Auth_SASL && \
        pear install pear/Net_SMTP

# configure apache
RUN rm -r /var/www/html
COPY .docker/php/vhost.conf /etc/apache2/sites-available/000-default.conf
COPY .docker/php/uploads.ini /usr/local/etc/php/conf.d/uploads.ini
RUN a2enmod rewrite
RUN docker-php-ext-configure zip --with-libzip
RUN docker-php-ext-install mbstring pdo pdo_mysql mysqli zip

# copy over files
COPY bin /var/www/bin
COPY public /var/www/public
COPY src /var/www/src
COPY templates /var/www/templates

# install plugins
ARG MPDF_VERSION=6.1.0
ENV MPDF_VERSION="${MPDF_VERSION}"
ARG PAYPAL_VERSION=3.9.1
ENV PAYPAL_VERSION="${PAYPAL_VERSION}"
ARG PAYPAL_USERNAME=""
ENV PAYPAL_USERNAME="${PAYPAL_USERNAME}"
ARG PAYPAL_PASSWORD=""
ENV PAYPAL_PASSWORD="${PAYPAL_PASSWORD}"
ARG PAYPAL_SIGNATURE=""
ENV PAYPAL_SIGNATURE="${PAYPAL_SIGNATURE}"
ARG TWITTER_VERSION=3.1.0
ENV TWITTER_VERSION="${TWITTER_VERSION}"

RUN mkdir /var/www/resources
## install jSignature plugin
RUN cd /var/www/public/js && \
        mkdir jSignature && \
        cd jSignature && \
        wget --quiet http://willowsystems.github.io/jSignature/jSignature.zip && \
        unzip -qq jSignature.zip && \
        rm jSignature.zip
## install mPDF
RUN cd /var/www/resources && \
        mkdir mPDF && \
        cd mPDF && \
        wget --quiet https://github.com/mpdf/mpdf/releases/download/v${MPDF_VERSION}/02-mPDF-v${MPDF_VERSION}-without-examples.zip && \
        unzip -qq 02-mPDF-v${MPDF_VERSION}-without-examples.zip && \
        rm 02-mPDF-v${MPDF_VERSION}-without-examples.zip
## install/configure paypal plugin
RUN cd /var/www/resources && \
        wget --quiet https://github.com/paypal/merchant-sdk-php/archive/v${PAYPAL_VERSION}.tar.gz && \
        tar -xf v${PAYPAL_VERSION}.tar.gz && \
        rm v${PAYPAL_VERSION}.tar.gz
RUN cd /var/www/resources/merchant-sdk-php-${PAYPAL_VERSION}/samples && \
        php -f install.php && \
        sed -i 's/mode = sandbox/mode = live/' sdk_config.ini && \
        sed -i 's/acct1.UserName =/acct1.UserName = ${PAYPAL_USERNAME}/' sdk_config.ini && \
        sed -i 's/acct1.Password =/acct1.Password = ${PAYPAL_PASSWORD}/' sdk_config.ini && \
        sed -i 's/acct1.Signature =/acct1.Signature = ${PAYPAL_SIGNATURE}/' sdk_config.ini
## install/configure ua parser plugin
RUN cd /var/www/resources && \
        wget --quiet https://github.com/cbschuld/Browser.php/archive/master.zip && \
        unzip -qq master.zip && \
        rm master.zip
## install browscap ini
RUN cd /var/www/resources && \
        wget --quiet http://browscap.org/stream?q=PHP_BrowsCapINI && \
        mv stream\?q\=PHP_BrowsCapINI php_browscap.ini
## install/configure twitter plugin
RUN cd /var/www/resources && \
        wget --quiet https://github.com/jublonet/codebird-php/archive/${TWITTER_VERSION}.tar.gz && \
        tar -xf ${TWITTER_VERSION}.tar.gz && \
        rm ${TWITTER_VERSION}.tar.gz

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
RUN ln -s /var/www/content/main /var/www/public/img/main
RUN ln -s /var/www/content/reviews /var/www/public/img/reviews
RUN ln -s /var/www/content/albums /var/www/public/albums
RUN ln -s /var/www/content/blog /var/www/public/blog/posts
RUN ln -s /var/www/content/contracts /var/www/public/user/contracts
RUN ln -s /var/www/src/errors /var/www/public/errors
RUN chown -R www-data:www-data /var/www/

WORKDIR /var/www

CMD ["./bin/setup-database.sh"]