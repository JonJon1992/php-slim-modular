FROM php:8.2-fpm-buster AS php81

ENV DEBIAN_FRONTEND=noninteractive

RUN apt-get update &&\
        apt-get install -y --no-install-recommends \
        curl \
        git \
        gnupg2 \
        libpng-dev \
        libpq-dev \
        openssh-client \
        libzip-dev \
        apt-utils \
        libssl-dev \
        zlib1g-dev \
        nginx \
        zip \
        unzip \
        procps \
        python3 \
        tzdata \
        && cp -rp /usr/share/zoneinfo/America/Sao_Paulo /etc/localtime \
        && apt-get remove -y --purge tzdata \
        && rm -rf /var/lib/apt/lists/* ;

RUN set -ex; \
        docker-php-ext-install  \
        bcmath \
        mysqli \
        pdo_mysql \
        pdo_pgsql \
        gd \
        pgsql \
        pdo \
        zip;

RUN pecl install -o -f redis \
        &&  rm -rf /tmp/pear \
        &&  docker-php-ext-enable redis;

RUN mv /usr/local/etc/php/php.ini-production /usr/local/etc/php/php.ini

RUN sed -i -r \
        -e 's|(short_open_tag) = \w+|\1 = On|' \
        -e 's|(memory_limit) = \w+|\1 = 64M|' \
        -e 's|(post_max_size) = \w+|\1 = 16M|' \
        -e 's|(upload_max_filesize) = \w+|\1 = 16M|' \
        -e 's|;date.timezone =|date.timezone = America/Sao_Paulo|' \
        /usr/local/etc/php/php.ini;

RUN curl -sS https://getcomposer.org/installer \
       | php -- --install-dir=/usr/bin --filename=composer \
       && rm -rf /root/.composer
