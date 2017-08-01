<?php
use App\Http\Transformers\Api\v1\PageContentTransformer;
use App\Models\Site;
use App\Models\PageContent;
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

$factory->state(Page::class, 'withPageContent', function ($faker) {
    return [
        'draft_id' => function(array $page) {
            return factory(PageContent::class)->create([

            ])->getKey();
        },
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
    $parent = factory(Page::class)->states('withPageContent', 'isRoot')->create();
    return [
        'parent_id' => $parent->getKey(),
    ];
});

$factory->state(Page::class, 'withPublishedParent', function ($faker) {
    $parent = factory(Page::class)->states('withPageContent', 'isRoot', 'withPublishedContent')->create();
    return [
        'parent_id' => $parent->getKey(),
    ];
});

