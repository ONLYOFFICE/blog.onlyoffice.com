<?php

namespace DemocracyPoll;

require_once __DIR__ . '/includes/theme-functions.php';

/**
 * PSR-4 compatible autoloader.
 */
spl_autoload_register( static function( $class ) {
	if( str_starts_with( $class, __NAMESPACE__ . '\\' ) ){
		$folder = __DIR__ . '/classes';
		$path = str_replace( [ __NAMESPACE__, '\\' ], [ $folder, '/' ], $class );

		require "$path.php";
	}
} );

/**
 * We canNOT use PSR-4 compatible autoloader here because of legacy reason.
 */
spl_autoload_register(
	static function( $class ) {
		if( $class === \DemPoll::class ){
			require_once __DIR__ . "/classes/$class.php";
		}
	}
);


