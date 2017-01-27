<?php

$factory->define(App\Models\Route::class, function (Faker\Generator $faker) {
	return [
		'slug' => $faker->word
	];
});
