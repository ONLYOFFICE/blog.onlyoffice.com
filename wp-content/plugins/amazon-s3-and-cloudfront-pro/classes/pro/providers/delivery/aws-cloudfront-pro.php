<?php

namespace DeliciousBrains\WP_Offload_Media\Pro\Providers\Delivery;

use AS3CF_Utils;
use DeliciousBrains\WP_Offload_Media\Aws3\Aws\CloudFront\UrlSigner;
use DeliciousBrains\WP_Offload_Media\Items\Item;
use DeliciousBrains\WP_Offload_Media\Providers\Delivery\AWS_CloudFront;
use DeliciousBrains\WP_Offload_Media\Settings\Validator_Interface;
use WP_Error as AS3CF_Result;

class AWS_CloudFront_Pro extends AWS_CloudFront {

	/**
	 * @var array
	 */
	protected static $signed_urls_key_id_constants = array(
		'AS3CF_AWS_CLOUDFRONT_SIGNED_URLS_KEY_ID',
	);

	/**
	 * @var array
	 */
	protected static $signed_urls_key_file_path_constants = array(
		'AS3CF_AWS_CLOUDFRONT_SIGNED_URLS_KEY_FILE_PATH',
	);

	/**
	 * @var array
	 */
	protected static $signed_urls_object_prefix_constants = array(
		'AS3CF_AWS_CLOUDFRONT_SIGNED_URLS_OBJECT_PREFIX',
	);

	/**
	 * @inheritDoc
	 */
	public static function signed_urls_support_desc() {
		return __( 'Private Media Supported', 'amazon-s3-and-cloudfront' );
	}

	/**
	 * Title used in various places for enabling Signed URLs.
	 *
	 * @return string
	 */
	public static function signed_urls_option_name() {
		return __( 'Serve Private Media from CloudFront', 'amazon-s3-and-cloudfront' );
	}

	/**
	 * Get the URL for the CloudFront Signed URLs doc.
	 *
	 * @param string $section Optional section to go to in page.
	 *
	 * @return string
	 */
	public static function get_signed_urls_setup_doc_url( string $section = '' ): string {
		global $as3cf;

		return $as3cf::dbrains_url(
			'/wp-offload-media/doc/serve-private-media-signed-cloudfront-urls/',
			array( 'utm_campaign' => 'support+docs' ),
			$section
		);
	}

	/**
	 * Description used in various places for enabling Signed URLs.
	 *
	 * @return string
	 */
	public static function signed_urls_option_description(): string {
		return sprintf(
			__( 'Prevents public access to certain media files by ensuring they are only accessible via signed URLs that expire shortly after delivery. <a href="%1$s" target="_blank">How to configure private media in CloudFront</a>', 'amazon-s3-and-cloudfront' ),
			static::get_signed_urls_setup_doc_url()
		);
	}

	/**
	 * Notice text for when a private file can be accessed using an unsigned URL.
	 *
	 * @return string
	 */
	public static function get_unsigned_url_can_access_private_file_desc(): string {
		return sprintf(
			__(
				'Private media is currently exposed through unsigned URLs. Restore privacy by verifying that the <strong>%1$s</strong> matches the CloudFront behavior. <a href="%2$s" target="_blank">Read more</a>',
				'amazon-s3-and-cloudfront'
			),
			static::signed_urls_object_prefix_name(),
			static::get_signed_urls_setup_doc_url( 'create-behavior' )
		);
	}

	/**
	 * Title used in various places for the Signed URLs Key ID.
	 *
	 * @return string
	 */
	public static function signed_urls_key_id_name() {
		return __( 'Public Key ID', 'amazon-s3-and-cloudfront' );
	}

	/**
	 * Description used in various places for the Signed URLs Key ID.
	 *
	 * @return string
	 */
	public static function signed_urls_key_id_description() {
		return __( "Any files set to private need a signed URL that includes the Public Key ID from a Public Key that has been added to a CloudFront distribution's Trusted Key Group.", 'amazon-s3-and-cloudfront' );
	}

	/**
	 * Title used in various places for the Signed URLs Key File Path.
	 *
	 * @return string
	 */
	public static function signed_urls_key_file_path_name() {
		return __( 'Private Key File Path', 'amazon-s3-and-cloudfront' );
	}

	/**
	 * Description used in various places for the Signed URLs Key File Path.
	 *
	 * @return string
	 */
	public static function signed_urls_key_file_path_description() {
		return __( "Any files set to private need to have their URLs signed with the Private Key File whose Public Key has been uploaded to CloudFront and added to a distribution's Trusted Key Group.", 'amazon-s3-and-cloudfront' );
	}

	/**
	 * Description used in various places for the Signed URLs Private Object Prefix.
	 *
	 * @return string
	 */
	public static function signed_urls_object_prefix_description() {
		return __( 'Any files set to private will be stored with this path prepended to the configured bucket path. An Amazon CloudFront behaviour must then be set up to restrict public access to the files at this path.', 'amazon-s3-and-cloudfront' );
	}

	/**
	 * {@inheritDoc}
	 */
	public function get_signed_url( Item $as3cf_item, $path, $domain, $scheme, $timestamp, $headers = array() ) {
		if ( static::use_signed_urls_key_file() ) {
			$path = $as3cf_item->private_prefix() . $path;

			if ( $this->as3cf->private_prefix_enabled() ) {
				$item_path      = $this->as3cf->maybe_update_delivery_path( $path, $domain, $timestamp );
				$item_path      = AS3CF_Utils::encode_filename_in_path( $item_path );
				$private_prefix = AS3CF_Utils::trailingslash_prefix( static::get_signed_urls_object_prefix() );

				// If object in correct private prefix, sign it.
				if ( 0 === strpos( $item_path, $private_prefix ) ) {
					$url                   = $scheme . '://' . $domain . '/' . $item_path;
					$key_id                = static::get_signed_urls_key_id();
					$private_key_file_path = static::get_signed_urls_key_file_path();

					$cf_url_signer = new UrlSigner( $key_id, $private_key_file_path );

					return $cf_url_signer->getSignedUrl( $url, $timestamp );
				}
			}
		}

		// Not set up for signing or in different private prefix, punt to default implementation.
		return parent::get_signed_url( $as3cf_item, $path, $domain, $scheme, $timestamp, $headers );
	}

	/**
	 * Validate settings for serving signed URLs.
	 *
	 * @return AS3CF_Result
	 */
	protected function validate_signed_url_settings(): AS3CF_Result {
		if ( ! $this->as3cf->get_setting( 'enable-signed-urls' ) ) {
			return new AS3CF_Result( Validator_Interface::AS3CF_STATUS_MESSAGE_SUCCESS );
		}

		// Is the key ID set?
		if ( empty( $this->get_signed_urls_key_id() ) ) {
			return new AS3CF_Result(
				Validator_Interface::AS3CF_STATUS_MESSAGE_ERROR,
				sprintf(
					_x(
						'Private media cannot be delivered at the moment because required field <strong>%1$s</strong> is empty. <a href="%2$s" target="_blank">Read more</a>',
						'Delivery setting notice for issue with missing key ID',
						'amazon-s3-and-cloudfront'
					),
					static::signed_urls_key_id_name(),
					static::get_provider_service_quick_start_url() . '#configure-plugin'
				)
			);
		}

		$key_file_path   = $this->get_signed_urls_key_file_path();
		$key_file_notice = $this->as3cf->notices->find_notice_by_id( 'validate-signed-urls-key-file-path' );

		if ( empty( $key_file_path ) && empty( $key_file_notice ) ) {
			return new AS3CF_Result(
				Validator_Interface::AS3CF_STATUS_MESSAGE_ERROR,
				sprintf(
					_x(
						'Private media cannot be delivered at the moment because required field <strong>%1$s</strong> is empty. <a href="%2$s" target="_blank">Read more</a>',
						'Delivery setting notice for issue with empty / missing key file path',
						'amazon-s3-and-cloudfront'
					),
					static::signed_urls_key_file_path_name(),
					static::get_provider_service_quick_start_url() . '#configure-plugin'
				)
			);
		}

		// Did the signed url key file validation trigger any issues?
		if ( ! empty( $key_file_notice ) ) {
			return new AS3CF_Result(
				Validator_Interface::AS3CF_STATUS_MESSAGE_ERROR,
				sprintf(
					_x(
						'Private media cannot be delivered at the moment because the file provided in <strong>%1$s</strong> is invalid or inaccessible. <a href="%3$s" target="_blank">Read more</a>',
						'Delivery setting notice for issue with Key File Path',
						'amazon-s3-and-cloudfront'
					),
					static::signed_urls_key_file_path_name(),
					ucfirst( $this->as3cf->notices->get_short_message( $key_file_notice ) ),
					static::get_provider_service_quick_start_url() . '#configure-plugin'
				)
			);
		}

		// Do we have a private path?
		if ( empty( $this->get_signed_urls_object_prefix() ) ) {
			return new AS3CF_Result(
				Validator_Interface::AS3CF_STATUS_MESSAGE_ERROR,
				sprintf(
					_x(
						'Private media cannot be delivered at the moment because required field <strong>%1$s</strong> is empty. <a href="%2$s" target="_blank">Read more</a>',
						'Delivery setting notice for issue with missing Private Bucket Path',
						'amazon-s3-and-cloudfront'
					),
					static::signed_urls_object_prefix_name(),
					static::get_provider_service_quick_start_url() . '#configure-plugin'
				)
			);
		}

		return new AS3CF_Result( Validator_Interface::AS3CF_STATUS_MESSAGE_SUCCESS );
	}
}
