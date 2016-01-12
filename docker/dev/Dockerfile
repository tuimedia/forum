# Should be docker/dev/Dockerfile
FROM inanimatt/forum:develop

MAINTAINER Matt Robinson <matt@tuimedia.com>

# Disabled until 2.4.0 stable
# RUN pecl install -o -f xdebug \
#    && rm -rf /tmp/pear \
#    && echo "zend_extension=xdebug.so" >> /usr/local/etc/php/conf.d/custom.ini

# RUN git clone -b XDEBUG_2_4_0RC2 https://github.com/xdebug/xdebug.git /tmp/xdebug && cd /tmp/xdebug && phpize && ./configure -q && make --quiet install && cd - && rm -fr /tmp/xdebug && echo "zend_extension=xdebug.so" >> /usr/local/etc/php/conf.d/custom.ini

# COPY docker/dev/xdebug.ini /usr/local/etc/php/conf.d/99-custom-xdebug.ini

# xdebug
# EXPOSE 9001

CMD ["docker/dev/run_dev.sh"]
