<?php

namespace DeliciousBrains\WP_Offload_Media\Pro\API;

use Amazon_S3_And_CloudFront_Pro;
use DeliciousBrains\WP_Offload_Media\API\API as Lite_API;

abstract class API extends Lite_API {
	/** @var Amazon_S3_And_CloudFront_Pro */
	protected $as3cf;
}