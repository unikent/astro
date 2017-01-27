<?php

$factory->define(App\Models\Page::class, function (Faker\Generator $faker) {

	return [
		'title' => $faker->name,
		'options' => ['description'=> $faker->sentence()],
		'key_page' => 0,
		'published' => 0,
	];
});
