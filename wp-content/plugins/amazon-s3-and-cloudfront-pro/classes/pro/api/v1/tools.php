<?php

namespace DeliciousBrains\WP_Offload_Media\Pro\API\V1;

use DeliciousBrains\WP_Offload_Media\Pro\API\API;
use WP_Error;
use WP_REST_Request;
use WP_REST_Response;

class Tools extends API {
	/** @var int */
	protected static $version = 1;

	/** @var string */
	protected static $name = 'tools';

	/**
	 * Register REST API routes.
	 */
	public function register_routes() {
		register_rest_route(
			static::api_namespace(),
			static::route(),
			array(
				'methods'             => 'GET',
				'callback'            => array( $this, 'get_tools' ),
				'permission_callback' => array( $this, 'check_permissions' ),
			)
		);

		register_rest_route(
			static::api_namespace(),
			static::route(),
			array(
				'methods'             => 'PUT',
				'callback'            => array( $this, 'put_tools' ),
				'permission_callback' => array( $this, 'check_permissions' ),
			)
		);

		register_rest_route(
			static::api_namespace(),
			static::route(),
			array(
				'methods'             => 'DELETE',
				'callback'            => array( $this, 'delete_tools' ),
				'permission_callback' => array( $this, 'check_permissions' ),
			)
		);
	}

	/**
	 * Processes a REST GET request and returns the current tools.
	 *
	 * @param WP_REST_Request $request
	 *
	 * @return WP_REST_Response|mixed
	 */
	public function get_tools( WP_REST_Request $request ) {
		return $this->rest_ensure_response( 'get', static::name(), array(
			'tools' => $this->as3cf->get_tools_info(),
		) );
	}

	/**
	 * Processes a REST PUT request to perform an action on a tool and returns confirmation of whether it was ok or not.
	 *
	 * @param WP_REST_Request $request
	 *
	 * @return WP_REST_Response|mixed
	 */
	public function put_tools( WP_REST_Request $request ) {
		$data = $request->get_json_params();

		if ( empty( $data['id'] ) ) {
			return $this->rest_ensure_response( 'put', static::name(),
				new WP_Error( 'missing-tool-id', __( 'Tool ID not supplied.', 'amazon-s3-and-cloudfront' ) )
			);
		}

		if ( empty( $data['action'] ) ) {
			return $this->rest_ensure_response( 'put', static::name(),
				new WP_Error( 'missing-tool-action', __( 'Action not supplied.', 'amazon-s3-and-cloudfront' ) )
			);
		}

		if ( ! in_array( $data['action'], array( 'start', 'cancel', 'pause_resume' ) ) ) {
			return $this->rest_ensure_response( 'put', static::name(),
				new WP_Error( 'invalid-tool-action', __( 'Invalid tool action supplied.', 'amazon-s3-and-cloudfront' ) )
			);
		}

		$result = $this->as3cf->perform_tool_action( $data['id'], $data['action'] );

		if ( is_wp_error( $result ) ) {
			return $this->rest_ensure_response( 'put', static::name(), $result );
		}

		return $this->rest_ensure_response( 'put', static::name(), array(
			'ok'    => $result,
			'tools' => $this->as3cf->get_tools_info(),
		) );
	}

	/**
	 * Processes a REST DELETE request to dismiss a tool's errors.
	 *
	 * @param WP_REST_Request $request
	 *
	 * @return WP_REST_Response|mixed
	 */
	public function delete_tools( WP_REST_Request $request ) {
		$data = $request->get_json_params();

		if ( empty( $data['id'] ) ) {
			return $this->rest_ensure_response( 'delete', static::name(),
				new WP_Error( 'missing-tool-id', __( 'Tool ID not supplied.', 'amazon-s3-and-cloudfront' ) )
			);
		}

		if ( empty( $data['blog_id'] ) ) {
			return $this->rest_ensure_response( 'delete', static::name(),
				new WP_Error( 'missing-blog-id', __( 'Blog ID not supplied.', 'amazon-s3-and-cloudfront' ) )
			);
		}

		if ( empty( $data['source_type'] ) ) {
			return $this->rest_ensure_response( 'delete', static::name(),
				new WP_Error( 'missing-source-type', __( 'Source Type not supplied.', 'amazon-s3-and-cloudfront' ) )
			);
		}

		if ( empty( $data['source_id'] ) ) {
			return $this->rest_ensure_response( 'delete', static::name(),
				new WP_Error( 'missing-source-id', __( 'Source ID not supplied.', 'amazon-s3-and-cloudfront' ) )
			);
		}

		if ( ! isset( $data['errors'] ) ) {
			$data['errors'] = 'all';
		}

		$result = $this->as3cf->dismiss_tool_errors( $data['id'], $data['blog_id'], $data['source_type'], $data['source_id'], $data['errors'] );

		if ( is_wp_error( $result ) ) {
			return $this->rest_ensure_response( 'delete', static::name(), $result );
		}

		return $this->rest_ensure_response( 'delete', static::name(), array(
			'ok' => true,
		) );
	}
}
