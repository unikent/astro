<?php

use \App\Models\User;

$factory->define(App\Models\User::class, function (Faker\Generator $faker) {
	return [
		'username' => $faker->username,
		'name' => $faker->name,
		'settings' => new ArrayObject()
	];
});

$factory->state(User::class, User::ROLE_ADMIN, function (Faker\Generator $faker){
   return [
        'role' => User::ROLE_ADMIN
   ];
});

$factory->state(User::class, User::ROLE_USER, function (Faker\Generator $faker){
   return [
        'role' => User::ROLE_USER
   ];
});

$factory->state(User::class, User::ROLE_VIEWER, function (Faker\Generator $faker){
   return [
        'role' => User::ROLE_VIEWER
   ];
});
