<?php

/**
 * Configuration settings for the Astro Editor
 */

return [
	// URL patterns to use for generating URLs to view published pages,
	// eg. http://{domain}{path}
	'app_live_url_pattern' => env('APP_LIVE_URL_PATTERN', 'http://{domain}{path}'),

	// URL patterns to use for generating URLs to view draft pages,
	// eg. http://astro.test/draft/{domain}{path}
	'app_preview_url_pattern' => env('APP_PREVIEW_URL_PATTERN', 'http://astro.test/draft/{domain}{path}'),

	// URL to the Astro API
	'astro_api_url' => env('ASTRO_API_URL'),

	// URL to logout of the Editor
	'astro_logout_url' => env('ASTRO_LOGOUT_URL'),

	// URL for help pages
	'help_url' => env('HELP_PAGES_URL'),

	// URL for media help pages
	'help_media_url' => env('HELP_MEDIA_URL'),

	// enable heap analytics
	'enable_heap' => env('ENABLE_HEAP', false),

	// id for heap analytics
	'heap_app_id' => env('HEAP_APP_ID'),

	// enable hotjar analytics
	'enable_hotjar' => env('ENABLE_HOTJAR', false),

	// hotjar ID
	'hotjar_id' => env('HOTJAR_ID'),

	// URL for the JWT provider
	'auth_url' => env('AUTH_URL'),

	// optional instructions displayed when asigning roles within sites
	'add_user_note' => env('ADD_USER_NOTE'),

	// optional instructions when a user logs in via sso but doesnt have required attribute
	'sso_denied_message' => env('SSO_DENIED_MESSAGE'),

	// configuration for clearing site switcher
	'clearing' => [
		'sites' => preg_split('/\s*,\s*/', env('CLEARING_SITE_IDS', ''), -1, PREG_SPLIT_NO_EMPTY),
		'live_host' => env('CLEARING_LIVE_DOMAIN', 'www.kent.ac.uk'),
	],
];
