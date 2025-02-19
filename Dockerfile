# Stage 1: Builder
FROM php:8.2-fpm-buster AS builder

ENV DEBIAN_FRONTEND=noninteractive

# Instalar dependências necessárias para build
RUN apt-get update && apt-get install -y --no-install-recommends \
    curl git unzip zip libpng-dev libpq-dev libzip-dev && \
    docker-php-ext-install bcmath gd pdo pdo_mysql pdo_pgsql && \
    pecl install redis && docker-php-ext-enable redis

# Instalar o Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/bin --filename=composer

# Copiar dependências PHP para serem instaladas
WORKDIR /app
COPY composer.json /app/
RUN composer install --no-dev --optimize-autoloader

# Stage 2: Runtime
FROM php:8.2-fpm-buster AS runtime

# Configuração inicial
ENV DEBIAN_FRONTEND=noninteractive \
    APP_ENV=production \
    PHP_MEMORY_LIMIT=128M \
    PHP_UPLOAD_MAX_FILESIZE=16M

# Instalar dependências de runtime
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

# Configurar PHP
# COPY build/confs/php/opcache.ini /usr/local/etc/php/conf.d/opcache.ini
RUN mv /usr/local/etc/php/php.ini-production /usr/local/etc/php/php.ini && \
    sed -i -r \
    -e "s|(short_open_tag) = \w+|\1 = On|" \
    -e "s|(memory_limit) = \w+|\1 = ${PHP_MEMORY_LIMIT}|" \
    -e "s|(post_max_size) = \w+|\1 = ${PHP_UPLOAD_MAX_FILESIZE}|" \
    -e "s|;date.timezone =|date.timezone = America/Sao_Paulo|" \
    /usr/local/etc/php/php.ini

# Configurar logs do Nginx
# RUN ln -sf /dev/stdout /var/log/nginx/access.log && \
#     ln -sf /dev/stderr /var/log/nginx/error.log

# Configurar Fluent Bit (se necessário)
# RUN curl https://packages.fluentbit.io/fluentbit.key | apt-key add - \
#     && echo "deb https://packages.fluentbit.io/debian/buster buster main" >> /etc/apt/sources.list \
#     && apt-get update && apt-get install td-agent-bit -y --no-install-recommends && \
#     mkdir -p /var/log/fluentbit && rm -rf /var/lib/apt/lists/*
# COPY build/confs/fluentbit/* /etc/fluentbit/

# Copiar configurações do Nginx
# COPY build/confs/nginx/conf.d/default.conf /etc/nginx/conf.d/default.conf
# COPY build/confs/nginx/nginx.conf /etc/nginx/nginx.conf

# # Configurar acesso SSH (se necessário)
# RUN mkdir /root/.ssh
# COPY build/confs/ssh/id_rsa /root/.ssh
# COPY build/confs/ssh/id_rsa.pub /root/.ssh
# COPY build/confs/ssh/config /root/.ssh
# RUN touch /root/.ssh/known_hosts && ssh-keyscan github.com >> /root/.ssh/known_hosts
# RUN chmod -v 600 /root/.ssh/id_rsa

# Criar usuário sem privilégios
# RUN addgroup -g 1000 www && adduser -u 1000 -G www -D www-data
# RUN chown -R www-data:www-data /var/www

# # Trocar para o novo usuário
# USER www-data

# Copiar e configurar scripts
# COPY build/start.sh /start.sh
# RUN chmod +x /start.sh
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/bin --filename=composer
EXPOSE 80
# CMD ["sh", "/start.sh"]
