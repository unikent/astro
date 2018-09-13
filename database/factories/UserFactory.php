<?php

use \App\Models\User;

$factory->define(App\Models\User::class, function (Faker\Generator $faker) {
	return [
		'username' => $faker->username,
		'name' => $faker->name,
		'password' => $faker->password,
		'email' => $faker->email,
		'role' => 'admin',
		'settings' => new ArrayObject()
	];
});

$factory->state(User::class, 'admin', function (Faker\Generator $faker){
   return [
        'role' => 'admin'
   ];
});
