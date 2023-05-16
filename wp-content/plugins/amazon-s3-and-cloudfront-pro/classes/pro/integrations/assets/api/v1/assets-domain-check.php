<?php

namespace DeliciousBrains\WP_Offload_Media\Pro\Integrations\Assets\API\V1;

use DeliciousBrains\WP_Offload_Media\Pro\API\API;
use DeliciousBrains\WP_Offload_Media\Pro\Integrations\Assets\Domain_Check_Response;
use WP_REST_Request;
use WP_REST_Response;

class Assets_Domain_Check extends API {
	/** @var int */
	protected static $version = 1;

	/** @var string */
	protected static $name = 'assets-domain-check';

	/**
	 * Register REST API routes.
	 */
	public function register_routes() {
		register_rest_route(
			static::api_namespace(),
			static::route() . '(?P<key>[\w\d=]+)',
			array(
				'methods'             => 'GET',
				'callback'            => array( $this, 'get_domain_check' ),
				'permission_callback' => '__return_true', // public access
			)
		);
	}

	/**
	 * Respond to a GET request to the domain check route, with the given key.
	 *
	 * @param WP_REST_Request $request
	 *
	 * @return WP_REST_Response
	 */
	public function get_domain_check( WP_REST_Request $request ): WP_REST_Response {
		$response = new Domain_Check_Response( array(
			'key' => $request->get_param( 'key' ),
			'ver' => filter_input( INPUT_GET, 'ver' ), // must come in as url param
		) );
		$response->header( 'X-As3cf-Signature', $response->hashed_signature() );

		return $this->rest_ensure_response( 'get', static::name(), $response );
	}

	/**
	 * Get a URL to the domain check route, with the given key.
	 *
	 * @param string $key
	 *
	 * @return string
	 */
	public function get_url( string $key ): string {
		return rest_url( static::endpoint() . $key );
	}
}
