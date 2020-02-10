<?php

/**
 * Configuration settings for theme / definitions. Theme-dependent.
 */

return [
	// main website URL pattern. Sensible default is '//{domain}{path}'
	// but might be useful to change it if using a non-standard port,
	// of if developing in a subdirectory as eg. //localhost/{domain}{path}
	'app_url' => env('APP_URL', '//{domain}{path}'),

	// The base URL to where kent-theme-assets are hosted.
	'assets_base_url' => env('ASSETS_BASE_URL', '//kent.test'),

	// URL patterns to use for generating URLs to view published pages,
	// eg. http://{domain}{path}
	'app_live_url_pattern' => env('APP_LIVE_URL_PATTERN', 'http://{domain}{path}'),

	// URL patterns to use for generating URLs to view draft pages,
	// eg. http://astro.test/draft/{domain}{path}
	'app_preview_url_pattern' => env('APP_PREVIEW_URL_PATTERN', 'http://astro.test/draft/{domain}{path}'),

	//  The default placeholder image to use for missing images,
	// RELATIVE to assets_base_url
	'placeholder_image_url' => env('PLACEHOLDER_IMAGE_URL', '/kent-theme-assets/assets/images/placeholder.jpg'),

	// Set to true to enable google tag manager in templates
	'enable_tagmanager' => env('ENABLE_TAGMANAGER', false),

	// Set this to the http host which represents our beta site. That
	// host will have the beta bar displayed at the top fo any pages.
	'beta_host' => env('BETA_HOST', 'beta.kent.ac.uk'),

	// Should be set on deploy - used to add a cache-busting hash to
	// the end of assets
	'assets_cache_hash' => env('ASSETS_CACHE_HASH'),

	// URL for the Kent API (api.kent.ac.uk/api/v1)
	'kent_api_url' => env('KENT_API_URL', 'https://api.kent.ac.uk/api/v1'),

	# proxy that guzzle may need to use when making requests to Kent API
	'proxy_url' => env('PROXY_URL', ''),

	// number of seconds to cache dynamic field options, default to 5 minus (5 * 60)
	'dynamic_options_cache_time' => env('DYNAMIC_OPTIONS_CACHE_TIME', 5*60),

	// site id of the site containing guides and their taxonomies
	'guide_site_id' => env('GUIDE_SITE_ID', 0),

	// site id of the site containing the ug course page renderer
	'ug_course_page_site_id' => env('UG_COURSE_PAGE_SITE_ID', 0),

	// site id of the site containing the pg course page renderer
	'pg_course_page_site_id' => env('PG_COURSE_PAGE_SITE_ID', 0),
];
