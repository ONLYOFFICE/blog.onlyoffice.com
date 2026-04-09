FROM wordpress:6.9.4-php8.2-apache

# Install php-redis extension for Redis Object Cache
RUN apt-get update && \
    apt-get install -y --no-install-recommends $PHPIZE_DEPS && \
    pecl install redis && \
    docker-php-ext-enable redis && \
    apt-get purge -y $PHPIZE_DEPS && \
    apt-get autoremove -y && \
    rm -rf /var/lib/apt/lists/* /tmp/pear

RUN rm -rf /var/www/html/wp-content/*
COPY wp-content/ /var/www/html/wp-content/
