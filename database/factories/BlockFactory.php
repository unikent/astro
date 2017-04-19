<?php

$factory->define(App\Models\Block::class, function (Faker\Generator $faker) {
	return [
		'region_name' => 'test-region',
		'definition_name' => 'test-block',
		'definition_version' => 1,
	];
});
