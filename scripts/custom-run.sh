#!/bin/bash

chmod -R a-w /bitnami/wordpress/wp-content/
exec /opt/bitnami/scripts/apache/run.sh
