<?php

/**
 * Configuration settings for theme / definitions. Theme-dependent.
 */

return [
	// main website URL pattern. Sensible default is '//{domain}{path}'
	// but might be useful to change it if using a non-standard port,
	// of if developing in a subdirectory as eg. //localhost/{domain}{path}
	'app_url' => '//{domain}{path}',

	// The base URL to where kent-theme-assets are hosted.
	'assets_base_url' => '//kent.test',

	// URL patterns to use for generating URLs to view published pages,
	// eg. http://{domain}{path}
	'app_live_url_pattern' => 'http://{domain}{path}',

	// URL patterns to use for generating URLs to view draft pages,
	// eg. http://astro.test/draft/{domain}{path}
	'app_preview_url_pattern' => 'http://astro.test/draft/{domain}{path}',

	//  The default placeholder image to use for missing images,
	// RELATIVE to assets_base_url
	'placeholder_image_url' => '/kent-theme-assets/assets/images/placeholder.jpg',

	// Set to true to enable google tag manager in templates
	'enable_tagmanager' => false,

	// Set this to the http host which represents our beta site. That
	// host will have the beta bar displayed at the top fo any pages.
	'beta_host' => 'beta.kent.ac.uk',

	// Should be set on deploy - used to add a cache-busting hash to
	// the end of assets
	'assets_cache_hash' => '',

	// URL for the Kent API (api.kent.ac.uk/api/v1)
	'kent_api_url' => 'https://api.kent.ac.uk/api/v1',

];

