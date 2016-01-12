FROM php:7.0-fpm

MAINTAINER Matt Robinson <matt@tuimedia.com>

RUN curl http://nginx.org/keys/nginx_signing.key > /tmp/nginx_signing.key \
  && apt-key add /tmp/nginx_signing.key \
  && echo "deb http://nginx.org/packages/mainline/debian/ jessie nginx" >> /etc/apt/sources.list \
  && echo "deb-src http://nginx.org/packages/mainline/debian/ jessie nginx" >> /etc/apt/sources.list

RUN apt-get update \
  && apt-get install -y -qq \
    git \
    libcurl4-openssl-dev \
    libicu-dev \
    mysql-client \
    netcat \
    nginx \
    sudo \
    supervisor \
    zlib1g-dev \
  && apt-get clean \
  && rm -rf /var/lib/apt/lists/* \
  && docker-php-ext-install \
    bcmath \
    curl \
    intl \
    mbstring \
    opcache \
    pdo_mysql \
    zip \
    > /dev/null

# RUN git clone -b php7 https://github.com/phpredis/phpredis.git /tmp/phpredis && cd /tmp/phpredis && phpize && ./configure -q && make --quiet install && cd .. && rm -fr /tmp/phpredis \
#   && echo "extension=redis.so" >> /usr/local/etc/php/conf.d/custom.ini \
#   && echo "date.timezone=Europe/London" >> /usr/local/etc/php/conf.d/custom.ini

# This is how we'd normally do it but can't until the php7 branch is merged and a new version is released
# RUN pecl install -o -f redis \
#    && rm -rf /tmp/pear \
#    && echo "extension=redis.so" >> /usr/local/etc/php/conf.d/custom.ini \
#    && echo "date.timezone=Europe/London" >> /usr/local/etc/php/conf.d/custom.ini

RUN curl -sS https://getcomposer.org/installer | php \
  && mv composer.phar /usr/local/bin/composer \
  && chmod a+x /usr/local/bin/composer

EXPOSE 443
EXPOSE 80

ENV DOCUMENT_ROOT=/var/www/app/web \
  SERVER_NAME=forum.dev \
  SYMFONY__DATABASE_HOST=db \
  SYMFONY__DATABASE_NAME=forum \
  SYMFONY__DATABASE_USER=forum \
  SYMFONY__DATABASE_PASSWORD=password \
  GITHUB_OAUTH_TOKEN= \
  NGINX_KEY=/etc/nginx/ssl/nginx.key \
  NGINX_CERT=/etc/nginx/ssl/nginx.crt \
  APP_URL_PREFIX=forum \
  APP_ENV=prod

COPY . /var/www/app
WORKDIR /var/www/app

CMD ["docker/base/run.sh"]
