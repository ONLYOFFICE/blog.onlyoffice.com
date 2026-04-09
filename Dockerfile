FROM wordpress:6.9.4-php8.2-apache

# Install php-redis extension for Redis Object Cache
RUN apt-get update && \
    apt-get install -y --no-install-recommends $PHPIZE_DEPS && \
    pecl install redis && \
    docker-php-ext-enable redis && \
    apt-get purge -y $PHPIZE_DEPS && \
    apt-get autoremove -y && \
    rm -rf /var/lib/apt/lists/* /tmp/pear

# Replace default wp-content with our own in the WordPress source directory.
# The official entrypoint copies /usr/src/wordpress/ → /var/www/html/ on startup,
# so our wp-content must be placed here to survive the copy.
RUN rm -rf /usr/src/wordpress/wp-content/*
COPY wp-content/ /usr/src/wordpress/wp-content/
