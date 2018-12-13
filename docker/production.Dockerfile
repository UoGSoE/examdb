### STAGING/TEST BUILD

# Set up php dependancies
FROM composer:1.7 as vendor

RUN mkdir -p database/seeds
RUN mkdir -p database/factories

COPY composer.json composer.json
COPY composer.lock composer.lock

RUN composer install \
    --ignore-platform-reqs \
    --no-interaction \
    --no-plugins \
    --no-scripts \
    --prefer-dist

# Build JS/css assets
FROM node:latest as frontend

RUN node --version
RUN mkdir -p /app/public

COPY package.json webpack.mix.js package-lock.json .babelrc /app/
RUN mkdir /app/resources
COPY resources/ /app/resources/

WORKDIR /app

RUN yarn install
RUN yarn development

# And build the app
FROM uogsoe/soe-php-apache:7.2

COPY docker/start.sh /usr/local/bin/start
COPY docker/ldap.conf /etc/ldap/ldap.conf
COPY docker/uploads.ini /usr/local/etc/php/conf.d/uploads.ini

RUN chmod u+x /usr/local/bin/start

COPY . /var/www/html
COPY --from=vendor /app/vendor/ /var/www/html/vendor/
COPY --from=frontend /app/public/js/ /var/www/html/public/js/
COPY --from=frontend /app/public/css/ /var/www/html/public/css/
COPY --from=frontend /app/mix-manifest.json /var/www/html/mix-manifest.json

RUN rm -fr /var/www/html/public/images
RUN php /var/www/html/artisan storage:link
RUN php /var/www/html/artisan view:clear
RUN chown -R www-data:www-data /var/www/html/storage
RUN chown -R www-data:www-data /var/www/html/bootstrap/cache

CMD ["/usr/local/bin/start"]
