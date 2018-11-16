FROM uogsoe/soe-php-apache:7.2

COPY docker/start.sh /usr/local/bin/start
COPY docker/ldap.conf /etc/ldap/ldap.conf
COPY docker/uploads.ini /usr/local/etc/php/conf.d/uploads.ini

RUN chmod u+x /usr/local/bin/start

COPY . /var/www/html
RUN rm -fr /var/www/html/public/images
RUN mv /var/www/html/.env.docker-dev /var/www/html/.env
RUN composer install
RUN php /var/www/html/artisan key:generate
RUN php /var/www/html/artisan storage:link
RUN php /var/www/html/artisan view:clear
RUN chown -R www-data:www-data /var/www/html

CMD ["/usr/local/bin/start"]
