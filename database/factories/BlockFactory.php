<?php

$factory->define(App\Models\Block::class, function (Faker\Generator $faker) {

	return [
		'type_guid' => "9689fb47-834e-4d34-a8e3-06e4ea1b25bf",
		'fields' => ['content' => "<p>{$faker->paragraph()}</p>"],

		'parent_block' => 0,
		'section' => 0,
		'order' => 1,
	];
});
