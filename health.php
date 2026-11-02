<?php
/**
 * Liveness/readiness endpoint for Kubernetes probes and ALB health checks.
 *
 * Deliberately does NOT load WordPress (no wp-load.php): it must verify only
 * that Apache + mod_php are alive. Probing through full WordPress bootstrap
 * (/wp-login.php, /) made every probe hit the database, so any DB slowdown
 * stacked hundreds of in-flight probes across pods, exhausted Apache workers
 * and DB connections, and cascaded into pod restarts (observed 2026-07-28:
 * 1282 of 1430 requests during a load storm were health probes).
 * A database problem must surface as DB alarms, not as "all pods are dead".
 */
http_response_code(200);
header('Content-Type: text/plain');
header('Cache-Control: no-store');
echo 'ok';
