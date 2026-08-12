<?php
namespace AIOSEO\Plugin\Lite\Options;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use AIOSEO\Plugin\Common\Options as CommonOptions;

/**
 * Class that holds all sensitive options for AIOSEO Lite.
 *
 * Extends the Common SensitiveOptions with Lite-specific keys.
 *
 * @since 4.9.6
 */
class SensitiveOptions extends CommonOptions\SensitiveOptions {
	/**
	 * Lite-specific allowed keys.
	 *
	 * @since 4.9.6
	 *
	 * @var array
	 */
	private $liteKeys = [
		'connectKey',
		'connectToken'
	];

	/**
	 * Class constructor.
	 *
	 * @since 4.9.6
	 */
	public function __construct() {
		$this->allowedKeys = array_merge( $this->allowedKeys, $this->liteKeys );

		parent::__construct();
	}
}