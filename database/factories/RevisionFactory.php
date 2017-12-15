<?php

use Astro\API\Models\Revision;
use Astro\API\Models\Page;
use Astro\API\Models\RevisionSet;
use Faker\Generator;

$factory->define(Revision::class, function(Generator $faker){
    return [
        'revision_set_id' => function($revision) {
            return factory(RevisionSet::class)->create()->getKey();
        },
        'title' => $faker->title,
        'layout_name' => 'kent-homepage',
        'layout_version' => 1,
        'blocks' => [],
        'options' => []
    ];
});
