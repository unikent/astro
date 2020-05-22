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

	//  The default size suffix to use for images eg '_1920w' or '_400x400'
	'image_version_suffix' => env('IMAGE_VERSION_SUFFIX', '_2000w'),

	//  The default size suffix to use for video placeholder images eg '_1920w' or '_400x400'
	'video_version_suffix' => env('VIDEO_VERSION_SUFFIX', '_800x450'),

	//  The default size suffix to use for square images eg '_400x400'
	'square_version_suffix' => env('SQUARE_VERSION_SUFFIX', '_400x400'),

	//  The default size suffix to use for inline images
	'inline_version_suffix' => env('INLINE_VERSION_SUFFIX', '_inline'),

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

	// site id of the site containing the ug profile renderer
	'ug_profile_page_site_id' => env('UG_PROFILE_PAGE_SITE_ID', 0),

	// site id of the site containing the pg profile renderer
	'pg_profile_page_site_id' => env('PG_PROFILE_PAGE_SITE_ID', 0),

	// site id of the site containing the modules renderer
	'module_page_site_id' => env('MODULE_PAGE_SITE_ID', 0),

    'cache' => [
        'courses' => [
            'all' => env('CACHE_COURSES_ALL_SECS', 0),  // seconds to cache list of all courses
            'single' => env('CACHE_COURSES_SINGLE_SECS', 0),  // seconds to cache individual course details
        ],
        'events' => [
            'single' => env('CACHE_EVENTS_SINGLE_SECS', 0),  // seconds to cache single event details
            'all' => env('CACHE_EVENTS_ALL_SECS', 0),  // seconds to cache list of events for
        ],
        'modules' => [
            'collections' => env('CACHE_MODULES_COLLECTIONS_SECS', 0),  // seconds to cache the list of collections for
            'subjects' => env('CACHE_MODULES_SUBJECTS_SECS', 0),  // seconds to cache the list of subjects for
            'modules_collection_index' => env('CACHE_MODULES_COLLECTION_INDEX_SECS', 0),  // seconds to cache the index of module collections for
            'module' => env('CACHE_MODULE_SECS', 0),  // seconds to cache the full individual module details for
        ],
        'student_profiles' => [
            'all' => env('CACHE_STUDENT_PROFILES_ALL_SECS', 0),  //  seconds to cache a listing of student profiles for
            'single' => env('CACHE_STUDENT_PROFILES_SINGLE_SECS', 0),  // seconds to cache full details of a single student profile
        ],
        'maps' => [
            'features' => env('CACHE_MAPS_FEATURES_SECS', 0),  // seconds to cache the listing of all maps features for
        ],
        'scholarship_minutes' => env('CACHE_SCHOLARSHIPS_MINUTES', 0),  // minutes to cache scholarship listing and details for,
		'scholarships' => [
			'all' => env('CACHE_SCHOLARSHIPS_ALL_SECS', 0),  // seconds to cache the listing of all scholarships for,
			'single' => env('CACHE_SCHOLARSHIPS_SINGLE_SECS', 0),  // seconds to cache a cholarship page for
		]
    ],

	// site id of the site containing the events renderer
	'event_page_site_id' => env('EVENT_PAGE_SITE_ID', 0),

	// site id of the site containing the news renderer
	'news_page_site_id' => env('NEWS_PAGE_SITE_ID', 0),

	// site id of the site containing the country page renderer
	'country_page_site_id' => env('COUNTRY_PAGE_SITE_ID', 0),

	// site id of the site containing the scholarships page renderer
	'scholarships_page_site_id' => env('SCHOLARSHIPS_PAGE_SITE_ID', 0),

	// site id of the site containing the scholarships list renderer
	'scholarships_list_site_id' => env('SCHOLARSHIPS_LIST_SITE_ID', 0)
];
