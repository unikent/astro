<?php
use App\Models\Site;
use App\Models\Page;
use App\Models\Revision;

$factory->define(Page::class, function (Faker\Generator $faker) {
    $site = factory(Site::class)->create();
	return [
		'slug' => $faker->word,
        'site_id' => $site->id
	];
});

$factory->state(Page::class, 'isRoot', function ($faker) {
    return [
    	'slug' => null,
        'parent_id' => null
    ];
});


$factory->state(Page::class, 'withPublishedContent', function ($faker) {
    return [
        'published_id' => function(array $page) {
            return factory(Revision::class)->create([

            ])->getKey();
        },
    ];
});


$factory->state(Page::class, 'withPage', function ($faker) {
    return [
        'published_id' => function(array $page) {
            return factory(Revision::class)->create([

            ])->getKey();
        },
    ];
});

$factory->state(Page::class, 'withParent', function ($faker) {
    $parent = factory(Page::class)->states('withRevision', 'isRoot')->create();
    return [
        'parent_id' => $parent->getKey(),
    ];
});

$factory->state(Page::class, 'withPublishedParent', function ($faker) {
    $parent = factory(Page::class)->states('withRevision', 'isRoot', 'withPublishedContent')->create();
    return [
        'parent_id' => $parent->getKey(),
    ];
});

