<?php

$factory->define(App\Models\Block::class, function (Faker\Generator $faker) {
	return [
		'region_name' => 'test-region',
		'definition_name' => 'test-block',
		'definition_version' => 1,
	];
});

$factory->state(App\Models\Block::class, 'useTestBlock', function ($faker) {
    return [
    	'definition_name' => 'test-block',

    	'fields' => [
    		'title_of_widget' => 'Foobar',
	        'number_of_widgets' => 20,
	        'content' => $faker->sentence,
	    ],
    ];
});
