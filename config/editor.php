<?php

/**
 * Configuration settings for the Astro Editor
 */

return [
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
];
