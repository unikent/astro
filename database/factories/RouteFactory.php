<?php

$factory->define(App\Models\Route::class, function (Faker\Generator $faker) {
	return [
		'slug' => $faker->word
	];
});

$factory->state(App\Models\Route::class, 'isRoot', function ($faker) {
    return [
    	'slug' => null,
        'parent_id' => null,
    ];
});

$factory->state(App\Models\Route::class, 'withPage', function ($faker) {
    $page = factory(App\Models\Page::class)->create();

    return [
        'page_id' => $page->getKey(),
    ];
});

$factory->state(App\Models\Route::class, 'withParent', function ($faker) {
    $parent = factory(App\Models\Route::class)->states('withPage', 'isRoot')->create();

    return [
        'parent_id' => $parent->getKey(),
    ];
});
