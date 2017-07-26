<?php
use App\Http\Transformers\Api\v1\PageContentTransformer;

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
    $page = factory(App\Models\PageContent::class)->create();

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

$factory->state(App\Models\Route::class, 'withPublishedParent', function ($faker) {
    $parent = factory(App\Models\Route::class)->states('withPage', 'isRoot')->create();
    $parent->page->publish(new PageContentTransformer);

    $parent = $parent->fresh();

    return [
        'parent_id' => $parent->getKey(),
    ];
});

$factory->state(App\Models\Route::class, 'withSite', function ($faker) {
    $site = factory(App\Models\Site::class)->states('withPublishingGroup')->create();

    return [
        'site_id' => $site->getKey(),
    ];
});
