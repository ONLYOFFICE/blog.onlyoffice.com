FROM wordpress:6.9.4-php8.2-apache

# The official entrypoint copies /usr/src/wordpress/ → /var/www/html/ on startup,
# so wp-content must be placed in the source dir to survive the copy.
COPY wp-content/ /usr/src/wordpress/wp-content/

# See apache-mpm-prefork.conf for rationale.
COPY apache-mpm-prefork.conf /etc/apache2/mods-available/mpm_prefork.conf

# Apache main config:
#   Timeout 60   — default 300s exceeds ALB idle timeout (180s).
#   KeepAlive Off — ALB reuses backend connections; keepalive ties up
#                   workers on idle sockets, costly with MaxRequestWorkers=40.
#
# Remove opcache-recommended.ini: PHP loads conf.d/*.ini alphabetically, so
# this upstream file overrides our chart-mounted custom.ini. All values it
# sets (memory_consumption, max_accelerated_files, interned_strings_buffer,
# revalidate_freq) are explicitly defined in custom.ini.
#
# php-redis: required by the Redis Object Cache plugin.
#
# object-cache.php drop-in: must match the installed plugin version,
# otherwise the plugin disables itself on activation.
RUN set -eux; \
    sed -i 's/^Timeout .*/Timeout 60/' /etc/apache2/apache2.conf; \
    sed -i 's/^KeepAlive .*/KeepAlive Off/' /etc/apache2/apache2.conf; \
    rm /usr/local/etc/php/conf.d/opcache-recommended.ini; \
    apt-get update; \
    apt-get install -y --no-install-recommends $PHPIZE_DEPS; \
    pecl install redis; \
    docker-php-ext-enable redis; \
    apt-get purge -y --auto-remove $PHPIZE_DEPS; \
    rm -rf /var/lib/apt/lists/* /tmp/pear; \
    cp /usr/src/wordpress/wp-content/plugins/redis-cache/includes/object-cache.php \
       /usr/src/wordpress/wp-content/object-cache.php
