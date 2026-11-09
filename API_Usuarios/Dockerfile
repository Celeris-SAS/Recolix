FROM php:8.3-apache

RUN docker-php-ext-install pdo_mysql mysqli \
 && a2enmod rewrite headers \
 && echo 'ServerTokens Prod' >> /etc/apache2/apache2.conf \
 && echo 'ServerSignature Off' >> /etc/apache2/apache2.conf

COPY API_Usuarios/docker/apache.conf /etc/apache2/sites-available/000-default.conf

WORKDIR /var/www/html
COPY API_Usuarios/ /var/www/html/
COPY conexion.php /var/www/conexion.php
COPY extra/ /var/www/html/recolix/extra/
COPY extra/ /var/www/extra/
COPY Backoffice/verificacion_sesion.php /var/www/Backoffice/verificacion_sesion.php
COPY API_Gestion/Modelo/centroAcopioModel.php /var/www/API_Gestion/Modelo/centroAcopioModel.php

RUN mkdir -p /var/www/sessions \
 && echo "session.save_path = /var/www/sessions" > /usr/local/etc/php/conf.d/session-path.ini \
 && chown -R www-data:www-data /var/www

EXPOSE 80
