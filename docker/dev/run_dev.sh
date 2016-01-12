#!/bin/bash

umask 0001

echo "Preparing configuration"

mkdir -p /etc/nginx/ssl
cp /var/www/app/docker/base/supervisor.conf /etc/supervisor/conf.d/supervisor.conf
cp /var/www/app/docker/dev/nginx.conf /etc/nginx/nginx.conf
cp /var/www/app/docker/base/nginx-app.conf /etc/nginx/conf.d/nginx-app.conf
cp /var/www/app/docker/base/php.ini /usr/local/etc/php.ini
cp /var/www/app/docker/base/self_cert.config /etc/nginx/ssl/self_cert.config

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
    /tmp/forum/cache \
    /tmp/forum/logs \
    /tmp/forum/sessions

chown -R www-data:www-data /tmp/forum
chmod -R g+rwX /tmp/forum

echo "Saving environment"
env > /var/www/app/app/config/env

echo "Checking composer dependencies"
cd /var/www/app
composer install -n

echo "Waiting for database"
while ! nc -z $SYMFONY__DATABASE_HOST 3306; do sleep 1; done

# if in dev environment, repopulate search and load initial DB
if [ "$APP_ENV" == "dev" ]
then
    echo "Loading test database"
    mysqldump -h $SYMFONY__DATABASE_HOST --ssl_ca=app/Resources/ssl/rds-combined-ca-bundle.pem --ssl-verify-server-cert -u $SYMFONY__DATABASE_USER -p$SYMFONY__DATABASE_PASSWORD $SYMFONY__DATABASE_NAME > var/db-previous.sql
    mysqladmin -h $SYMFONY__DATABASE_HOST --ssl_ca=app/Resources/ssl/rds-combined-ca-bundle.pem --ssl-verify-server-cert -u $SYMFONY__DATABASE_USER -p$SYMFONY__DATABASE_PASSWORD -f drop $SYMFONY__DATABASE_NAME
    mysqladmin -h $SYMFONY__DATABASE_HOST --ssl_ca=app/Resources/ssl/rds-combined-ca-bundle.pem --ssl-verify-server-cert -u $SYMFONY__DATABASE_USER -p$SYMFONY__DATABASE_PASSWORD create $SYMFONY__DATABASE_NAME
    mysql -h $SYMFONY__DATABASE_HOST --ssl_ca=app/Resources/ssl/rds-combined-ca-bundle.pem --ssl-verify-server-cert -u $SYMFONY__DATABASE_USER -p$SYMFONY__DATABASE_PASSWORD $SYMFONY__DATABASE_NAME < var/initialdb.sql

    echo "Migrating database"
    bin/console doc:mig:mig -n
else
    echo "Migrating database"
    bin/console doc:mig:mig -n
fi

chown -R www-data:www-data /tmp/forum
chmod -R g+rwX /tmp/forum

echo "Starting supervisor"
exec /usr/bin/supervisord -n -c /etc/supervisor/supervisord.conf

