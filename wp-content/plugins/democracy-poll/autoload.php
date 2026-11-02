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


// For backward compatibility. !After spl_autoload_register()
class_alias( Poll::class, \DemPoll::class );
class_alias( Support\Helpers::class, Helpers\Helpers::class );
class_alias( Support\Kses::class, Helpers\Kses::class );
