<?php

$factory->define(App\Models\PageContent::class, function (Faker\Generator $faker) {
	return [
		'title' => $faker->sentence(3),
		'options' => [ 'description'=> $faker->sentence() ],

		'layout_name' => 'test-layout',
		'layout_version' => 1,
	];
});
