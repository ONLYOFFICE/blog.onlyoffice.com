<?php

namespace DeliciousBrains\WP_Offload_Media\Pro\Integrations;

use Amazon_S3_And_CloudFront_Pro;
use DeliciousBrains\WP_Offload_Media\Integrations\Core;
use DeliciousBrains\WP_Offload_Media\Items\Download_Handler;
use DeliciousBrains\WP_Offload_Media\Items\Item;
use DeliciousBrains\WP_Offload_Media\Pro\Items\Remove_Provider_Handler;
use WP_Error;

class Core_Pro extends Core {
	/**
	 * @var Amazon_S3_And_CloudFront_Pro
	 */
	protected $as3cf;

	/**
	 * @inheritDoc
	 */
	public function setup() {
		parent::setup();

		add_action( 'as3cf_pre_handle_item_' . Remove_Provider_Handler::get_item_handler_key_name(), array( $this, 'maybe_download_files' ), 10, 3 );
	}

	/**
	 * Before removing from provider, maybe download files.
	 *
	 * @handles as3cf_pre_handle_item_remove-provider
	 *
	 * @param bool  $cancel     Should the action on the item be cancelled?
	 * @param Item  $as3cf_item The item that the action is being handled for.
	 * @param array $options    Handler dependent options that may have been set for the action.
	 *
	 * @return bool|WP_Error
	 */
	public function maybe_download_files( $cancel, Item $as3cf_item, array $options ) {
		if ( false === $cancel && ! empty( $options['verify_exists_on_local'] ) ) {
			$download_handler = $this->as3cf->get_item_handler( Download_Handler::get_item_handler_key_name() );
			$result           = $download_handler->handle( $as3cf_item );

			// If there was any kind of error, then remove from provider should not proceed.
			// Because this is an unexpected error, bubble it.
			if ( is_wp_error( $result ) ) {
				return $result;
			}
		}

		return $cancel;
	}
}
