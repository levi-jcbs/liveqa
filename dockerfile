FROM php:8.1-apache-bullseye

RUN docker-php-ext-install mysqli sockets pdo pdo_mysql

COPY ./html /var/www/html/

COPY ./apache/000-default.conf /etc/apache2/sites-available/



