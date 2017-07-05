<?php
$factory->define(App\Models\User::class, function (Faker\Generator $faker) {
	return [
		'username' => $faker->username,
		'name' => $faker->name,
		'settings' => new ArrayObject()
	];
});
