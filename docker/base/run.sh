#!/bin/bash

umask 001

echo "Copying configuration"

mkdir -p /etc/supervisor/conf.d /etc/nginx/ssl /usr/local/etc/
cp docker/base/supervisor.conf /etc/supervisor/conf.d/supervisor.conf
cp docker/base/nginx.conf /etc/nginx/nginx.conf
cp docker/base/nginx-app.conf /etc/nginx/conf.d/nginx-app.conf
cp docker/base/php.ini /usr/local/etc/php.ini
cp docker/base/self_cert.config /etc/nginx/ssl/self_cert.config

echo "Configuring web server"

sed -i "s|DOCUMENT_ROOT|$DOCUMENT_ROOT|g;s|INDEX_FILE|$INDEX_FILE|g;s|SERVER_NAME|$SERVER_NAME|g;s|NGINX_CERT|$NGINX_CERT|g;s|NGINX_KEY|$NGINX_KEY|g;s|APP_URL_PREFIX|$APP_URL_PREFIX|g;s|DOCKER_APP_ENV|$APP_ENV|g" /etc/nginx/conf.d/nginx-app.conf && \
    sed -i "s/SERVER_NAME/$SERVER_NAME/g" /etc/nginx/ssl/self_cert.config

if [ ! -f "$NGINX_KEY" ]
then
    echo "Creating self-signed SSL key"
    mkdir -p /etc/nginx/ssl
    cd /etc/nginx/ssl
    openssl req -x509 -nodes -days 365 -newkey rsa:2048 -keyout nginx.key -out nginx.crt -config self_cert.config
fi

echo "Setting file permissions"
mkdir -p \
    /tmp/forum/cache/dev \
    /tmp/forum/cache/prod \
    /tmp/forum/logs \
    /tmp/forum/sessions \
    /var/www/.composer

chown -R www-data:www-data /tmp/forum /var/www/app /var/www/.composer
chmod -R g+rwX /tmp/forum /var/www/app /var/www/.composer/

echo "Checking composer dependencies"
cd /var/www/app
sudo -u www-data composer config -g github-oauth.github.com $GITHUB_OAUTH_TOKEN
sudo -u www-data composer install -n

echo "Saving environment"
env > /var/www/app/app/config/env

echo "Waiting for database"
while ! nc -z $SYMFONY__DATABASE_HOST 3306; do sleep 1; done

echo "Migrating database"
sudo -u www-data bin/console doc:mig:mig -n

chown -R www-data:www-data /tmp/forum
chmod -R g+rwX /tmp/forum

echo "Starting supervisor"
exec /usr/bin/supervisord -n -c /etc/supervisor/supervisord.conf

