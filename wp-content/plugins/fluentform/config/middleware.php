<?php

if ( ! defined( 'ABSPATH' ) ) { exit; }

return [
    'global' => [
        'before' => [
            function ($request, $next) {
                return $next($request->forget('query_timestamp'));
            }
        ],
        'after' => [
            function ($response, $next) {
                return $next($response);
            }
        ]
    ],
    'route' => [
        'before' => [
            'auth' => function($request, $next) {
                if (is_user_logged_in()) {
                    return $next($request);
                }
            },
            'can' => function($request, $next, ...$roles) {
                if (array_intersect($roles, ['create', 'update'])) {
                    return $next($request);
                }
            },
        ],
        'after' => [
            'logger' => function($response, $next) {
                return $next($response);
            }
        ],
    ]
];
