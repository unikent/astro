<?php

/**
 * Default site tree. If added to a new site, has the structure:
 * /
 * /undergraduates
 * /undergraduates/2017
 * /undergraduates/2018
 * /postgraduates
 * /postgraduates/2017
 * /postgraduates/2018
 */
return [
	[
		'slug' => 'undergraduate',
		'title' => 'Undergraduates',
		'layout' => ['name' => 'test-layout', 'version' => 1],
		'children' => [
			[
				'slug' => '2017',
				'title' => '2017 Entry',
				'layout' => ['name' => 'test-layout', 'version' => 1]
			],
			[
				'slug' => '2018',
				'title' => '2018 Entry',
				'layout' => ['name' => 'test-layout', 'version' => 1]
			],
		]
	],
	[
		'slug' => 'postgraduate',
		'title' => 'Postgraduates',
		'layout' => ['name' => 'test-layout', 'version' => 1],
		'children' => [
			[
				'slug' => '2017',
				'title' => '2017 Entry',
				'layout' => ['name' => 'test-layout', 'version' => 1]
			],
			[
				'slug' => '2018',
				'title' => '2018 Entry',
				'layout' => ['name' => 'test-layout', 'version' => 1]
			],
		]
	]
];
