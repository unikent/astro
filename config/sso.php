<?php

/**
 * Configuration file if using sso
 */

return [
	// whether or not sso authentication endpoint should be enabled
	'enable_sso' => env('ENABLE_SSO', false),

	// the full path to where simplesamlphp service provider has been installed
	'simplesaml_sp_path' => env('SIMPLESAML_SP_PATH'),
];

