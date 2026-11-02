<?php
namespace AIOSEO\Plugin\Common\Api;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Exception for API endpoint errors.
 *
 * @since 4.9.6
 */
class ApiException extends \Exception {
	/**
	 * Machine-readable error code (e.g. 'no_content', 'post_not_found').
	 *
	 * @since 4.9.6
	 *
	 * @var string
	 */
	protected $errorCode;

	/**
	 * Class constructor.
	 *
	 * @since 4.9.6
	 *
	 * @param string $errorCode  Machine-readable error code.
	 * @param string $message    Human-readable error message.
	 * @param int    $httpStatus HTTP status code (defaults to 400).
	 */
	public function __construct( $errorCode, $message = '', $httpStatus = 400 ) {
		parent::__construct( $message, $httpStatus );

		$this->errorCode = $errorCode;
	}

	/**
	 * Returns the machine-readable error code.
	 *
	 * @since 4.9.6
	 *
	 * @return string
	 */
	public function getErrorCode() {
		return $this->errorCode;
	}
}