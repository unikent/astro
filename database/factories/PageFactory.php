<?php

$factory->define(App\Models\Page::class, function (Faker\Generator $faker) {
	return [
		'title' => $faker->name,
		'options' => [ 'description'=> $faker->sentence() ],

		'is_site' => 0,
		'is_published' => 0,

		'layout_name' => 'test-layout',
		'layout_version' => 1,
	];
});
