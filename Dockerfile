FROM 448869868355.dkr.ecr.sa-east-1.amazonaws.com/base-image:latest

WORKDIR /var/www

COPY . /var/www
RUN composer install
ARG WITH_XDEBUG
ARG WITH_COMPOSER

WORKDIR /var/www

RUN touch .env

RUN sed -i -r \
    -e 's|pm = static|pm = dynamic|' \
    -e  's|pm.max_children = [0-9]+|pm.max_children = 16|' \
    /usr/local/etc/php-fpm.d/www.conf;

RUN \
    if ["$WITH_XDEBUG" = true] ; then \
    pecl install xdebug-3.2.0 && \
    docker-php-ext-enable xdebug; \
    fi
