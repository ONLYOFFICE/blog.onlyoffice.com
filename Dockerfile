FROM bitnami/wordpress:6.2.2-debian-11-r72

RUN rm -rf /opt/bitnami/wordpress/wp-content/*
COPY wp-content/ /opt/bitnami/wordpress/wp-content/
COPY scripts/custom-run.sh /opt/bitnami/scripts/custom-run.sh
