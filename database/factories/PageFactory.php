<?php

$factory->define(App\Models\Page::class, function (Faker\Generator $faker) {
	return [
		'title' => $faker->name,
		'options' => ['description'=> $faker->sentence()],
		'is_site' => 0,
		'published' => 0,
	];
});
