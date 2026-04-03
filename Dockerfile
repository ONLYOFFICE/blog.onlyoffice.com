FROM bitnamilegacy/wordpress:6.2.2-debian-11-r82

# Install php-redis extension for Redis Object Cache
USER root
RUN mkdir -p /var/lib/apt/lists/partial && \
    apt-get update && \
    apt-get install -y --no-install-recommends build-essential autoconf && \
    printf 'no\nno\nno\nno\nno\nno\n' | /opt/bitnami/php/bin/pecl install redis && \
    echo 'extension=redis.so' >> /opt/bitnami/php/etc/php.ini && \
    apt-get purge -y build-essential autoconf && \
    apt-get autoremove -y && \
    rm -rf /var/lib/apt/lists/* /tmp/pear
USER 1001

RUN rm -rf /opt/bitnami/wordpress/wp-content/*
COPY wp-content/ /opt/bitnami/wordpress/wp-content/

# Move object-cache.php drop-in aside during build.
# It will be activated by post-init script after wp-config.php is ready.
# This prevents RedisException during entrypoint wp-cli calls.
RUN mv /opt/bitnami/wordpress/wp-content/object-cache.php \
       /opt/bitnami/wordpress/wp-content/object-cache.php.disabled
