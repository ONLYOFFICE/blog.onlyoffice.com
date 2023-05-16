<?php

namespace DeliciousBrains\WP_Offload_Media\Pro\API\V1;

use DeliciousBrains\WP_Offload_Media\Pro\API\API;
use WP_Error;
use WP_REST_Request;
use WP_REST_Response;

class Licences extends API {
	/** @var int */
	protected static $version = 1;

	/** @var string */
	protected static $name = 'licences';

	/**
	 * Register REST API routes.
	 */
	public function register_routes() {
		register_rest_route(
			static::api_namespace(),
			static::route(),
			array(
				'methods'             => 'GET',
				'callback'            => array( $this, 'get_licences' ),
				'permission_callback' => array( $this, 'check_permissions' ),
			)
		);

		register_rest_route(
			static::api_namespace(),
			static::route(),
			array(
				'methods'             => 'POST',
				'callback'            => array( $this, 'post_licences' ),
				'permission_callback' => array( $this, 'check_permissions' ),
			)
		);

		register_rest_route(
			static::api_namespace(),
			static::route(),
			array(
				'methods'             => 'DELETE',
				'callback'            => array( $this, 'delete_licences' ),
				'permission_callback' => array( $this, 'check_permissions' ),
			)
		);
	}

	/**
	 * Processes a REST GET request to retrieve licence info.
	 *
	 * @param WP_REST_Request $request
	 *
	 * @return WP_REST_Response|mixed
	 */
	public function get_licences( WP_REST_Request $request ) {
		$data = $request->get_json_params();

		$force = ! empty( $data['force'] );

		return $this->rest_ensure_response( 'get', static::name(), array(
			'licences' => $this->as3cf->get_licences( $force ),
		) );
	}

	/**
	 * Processes a REST POST request to activate supplied licence and return resultant licence info.
	 *
	 * @param WP_REST_Request $request
	 *
	 * @return WP_REST_Response|mixed
	 */
	public function post_licences( WP_REST_Request $request ) {
		$data        = $request->get_json_params();
		$licence_key = empty( $data['licence'] ) ? '' : $data['licence'];
		$result      = $this->as3cf->activate_licence( $licence_key );

		if ( is_wp_error( $result ) ) {
			return $this->rest_ensure_response( 'post', static::name(), $result );
		}

		return $this->rest_ensure_response( 'post', static::name(), array(
			'licences' => $this->as3cf->get_licences(),
		) );
	}

	/**
	 * Processes a REST DELETE request to deactivate current licence and return resultant licence info.
	 *
	 * @param WP_REST_Request $request
	 *
	 * @return WP_REST_Response|mixed
	 */
	public function delete_licences( WP_REST_Request $request ) {
		if ( $this->as3cf->is_licence_constant() ) {
			return $this->rest_ensure_response( 'delete', static::name(),
				new WP_Error(
					'licence-constant',
					__( 'Your licence key is currently defined via a constant and must be removed manually.', 'amazon-s3-and-cloudfront' )
				)
			);
		}

		// We currently have one licence applied to a plugin install.
		$this->as3cf->remove_licence();

		return $this->rest_ensure_response( 'delete', static::name(), array(
			'licences' => $this->as3cf->get_licences(),
		) );
	}
}
