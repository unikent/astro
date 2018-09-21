<?php

use \App\Models\User;

$factory->define(App\Models\User::class, function (Faker\Generator $faker) {
	return [
		'username' => $faker->username,
		'name' => $faker->name,
		'settings' => new ArrayObject()
	];
});

$factory->state(User::class, 'admin', function (Faker\Generator $faker){
   return [
        'role' => 'admin'
   ];
});

$factory->state(User::class, 'viewer', function (Faker\Generator $faker){
   return [
        'role' => 'viewer'
   ];
});
