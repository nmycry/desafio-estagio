FROM php:8.5-apache

ARG UID=1000
ARG GID=1000

RUN apt-get update \
    && apt-get install -y --no-install-recommends git unzip \
    && rm -rf /var/lib/apt/lists/* \
    && docker-php-ext-install pdo_mysql \
    && a2enmod rewrite

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Apache escuta na 8080 e serve /public; rodando como www-data, uma porta
# acima de 1024 dispensa privilégios de root.
RUN sed -ri 's/^Listen 80$/Listen 8080/' /etc/apache2/ports.conf \
    && sed -ri \
        -e 's/<VirtualHost \*:80>/<VirtualHost *:8080>/' \
        -e 's!/var/www/html!/var/www/html/public!g' \
        /etc/apache2/sites-available/000-default.conf \
    && printf '<Directory /var/www/html/public>\n    AllowOverride All\n</Directory>\n' \
        > /etc/apache2/conf-enabled/app.conf \
    && printf 'display_errors=Off\nlog_errors=On\n' > /usr/local/etc/php/conf.d/app.ini

# www-data passa a ter o UID/GID do usuário do host: arquivos criados
# pelo container (vendor/, caches) pertencem a ele, não ao root.
RUN groupmod -o -g "${GID}" www-data \
    && usermod -o -u "${UID}" -g "${GID}" www-data \
    && chown -R www-data:www-data /var/www /var/run/apache2 /var/lock/apache2 /var/log/apache2

ENV COMPOSER_HOME=/tmp/composer

USER www-data
WORKDIR /var/www/html

EXPOSE 8080
