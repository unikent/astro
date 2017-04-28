<?php

$factory->define(App\Models\Page::class, function (Faker\Generator $faker) {
	return [
		'title' => $faker->sentence(3),
		'options' => [ 'description'=> $faker->sentence() ],

		'is_published' => 0,

		'layout_name' => 'test-layout',
		'layout_version' => 1,
	];
});
