<?php
use Astro\API\Models\Site;
use Astro\API\Models\Page;
use Astro\API\Models\Revision;

$factory->define(Page::class, function (Faker\Generator $faker) {
    $site = factory(Site::class)->create();
	return [
		'slug' => null,
        'site_id' => $site->id,
        'parent_id' => null,
        'version' => Page::STATE_DRAFT
	];
});

$factory->state(Page::class, 'withRevision', function($faker){
    return [
        'revision_id' => function(){
            return factory(Revision::class)->create()->getKey();
        }
    ];
});

$factory->state(Page::class, 'withParent', function ($faker) {
    $parent = factory(Page::class)->states('withRevision')->create();
    return [
        'parent_id' => $parent->getKey(),
    ];
});


$factory->state(Page::class, 'withSite', function ($faker) {
    $site = factory(Site::class)->create();
    $parent = factory(Page::class)->states('withRevision')->create();
    return [
        'site_id' => $site->id
    ];
});


