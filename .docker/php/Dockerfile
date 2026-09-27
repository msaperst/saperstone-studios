FROM node:22-bookworm-slim AS assets

# Build minified copies of first-party assets for production. Source files stay
# readable in the repository/local development environment.
WORKDIR /build
COPY public /build/public
RUN find /build/public/js -maxdepth 1 -type f -name '*.js' ! -name '*.min.js' -print0 | \
    while IFS= read -r -d '' file; do npx --yes terser@5.44.0 "$file" --compress --mangle --comments false -o "${file%.js}.min.js"; done && \
    find /build/public/css -maxdepth 1 -type f -name '*.css' ! -name '*.min.css' -print0 | \
    while IFS= read -r -d '' file; do npx --yes clean-css-cli@5.6.3 -o "${file%.css}.min.css" "$file"; done

FROM composer:2 AS composer

FROM php:8.4-apache-bookworm

# setup additional tooling
RUN apt-get update && \
    apt-get upgrade -y && \
    apt-get install -y --no-install-recommends \
        default-mysql-client \
        gnupg \
        imagemagick \
        libonig-dev \
        libpng-dev \
        libzip-dev \
        unzip \
        wget \
        zip && \
    rm -rf /var/lib/apt/lists/*

# Copy Composer from its official image instead of executing a downloaded installer.
COPY --from=composer /usr/bin/composer /usr/local/bin/composer
COPY .docker/php/composer.json /var/www/

# configure apache
RUN rm -r /var/www/html
COPY .docker/php/000-default.conf /etc/apache2/sites-enabled/000-default.conf
COPY .docker/php/default-ssl.conf /etc/apache2/sites-enabled/default-ssl.conf
# Load site security overrides after Debian's default security.conf so our
# ServerTokens/ServerSignature policy is the effective final configuration.
COPY .docker/php/security-headers.conf /etc/apache2/conf-available/zz-saperstone-security.conf
COPY .docker/php/uploads.ini /usr/local/etc/php/conf.d/uploads.ini
COPY .docker/php/security.ini /usr/local/etc/php/conf.d/security.ini
RUN a2enmod headers rewrite expires speling ssl && a2enconf zz-saperstone-security
RUN docker-php-ext-install mbstring pdo pdo_mysql mysqli zip gd exif

# copy over files
COPY bin /var/www/bin
COPY --from=assets /build/public /var/www/public
COPY src /var/www/src
COPY templates /var/www/templates

# Fail the image build if generated assets or the PHP production URL selection
# disappear. Local Compose hides these image assets with its source bind mount.
RUN php -r 'require "/var/www/src/Strings.php"; \
    foreach (["/js/album.js" => "/js/album.min.js", "/css/saperstone-studios.css" => "/css/saperstone-studios.min.css"] as $source => $minified) { \
        if (!is_file("/var/www/public" . $source) || !is_file("/var/www/public" . $minified) || \
            !str_starts_with(Strings::assetUrl($source, "/var/www/public"), $minified . "?v=")) { \
            fwrite(STDERR, "Missing production asset or incorrect URL: " . $minified . PHP_EOL); exit(1); \
        } \
    }'

# install plugins
RUN cd /var/www && \
    composer install --no-dev --prefer-dist --no-interaction --no-progress --optimize-autoloader

## install jSignature plugin
RUN cd /var/www/public/js && \
        mkdir jSignature && \
        cd jSignature && \
        wget --quiet https://willowsystems.github.io/jSignature/jSignature.zip && \
        unzip -qq jSignature.zip && \
        rm jSignature.zip

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
