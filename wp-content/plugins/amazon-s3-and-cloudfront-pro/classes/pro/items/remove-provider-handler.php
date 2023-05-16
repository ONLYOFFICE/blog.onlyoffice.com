<?php

namespace DeliciousBrains\WP_Offload_Media\Pro\Items;

use DeliciousBrains\WP_Offload_Media\Items\Remove_Provider_Handler as Remove_Provider_Handler_Lite;

class Remove_Provider_Handler extends Remove_Provider_Handler_Lite {
	/**
	 * The default options that should be used if none supplied.
	 *
	 * @return array
	 */
	public static function default_options() {
		return array(
			'object_keys'            => array(),
			'offloaded_files'        => array(),
			'verify_exists_on_local' => true,
		);
	}
}