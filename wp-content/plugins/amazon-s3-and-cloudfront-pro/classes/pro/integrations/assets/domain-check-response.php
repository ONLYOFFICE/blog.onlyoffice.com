<?php

namespace DeliciousBrains\WP_Offload_Media\Pro\Integrations\Assets;

use AS3CF_Utils;
use DeliciousBrains\WP_Offload_Media\Settings\Exceptions\Signature_Verification_Exception;
use WP_REST_Response;

class Domain_Check_Response extends WP_REST_Response {

	/**
	 * Verify that this response is valid for the given hashed signature.
	 *
	 * @param string $signature A hashed signature to verify this response against.
	 *
	 * @throws Signature_Verification_Exception
	 */
	public function verify_signature( string $signature ) {
		if ( ! wp_check_password( $this->raw_signature(), $signature ) ) {
			throw new Signature_Verification_Exception(
				__( 'Invalid request signature.', 'amazon-s3-and-cloudfront' )
			);
		}
	}

	/**
	 * Get the hashed signature for this response.
	 *
	 * @return string
	 */
	public function hashed_signature(): string {
		return wp_hash_password( $this->raw_signature() );
	}

	/**
	 * Get the raw signature for this response.
	 *
	 * @return string
	 */
	protected function raw_signature(): string {
		return AS3CF_Utils::reduce_url( network_home_url() ) . '|' . json_encode( $this->jsonSerialize() ) . '|' . AUTH_SALT;
	}
}
