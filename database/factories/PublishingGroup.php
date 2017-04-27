<?php

$factory->define(App\Models\PublishingGroup::class, function (Faker\Generator $faker) {
	return [
		'name' => $faker->name,
	];
});
