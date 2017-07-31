<?php

use App\Models\Revision;
use App\Models\PageContent;
use Faker\Generator;

$factory->define(Revision::class, function($faker){
    return [
        'page_content_id' => function($revision) {
            return factory(PageContent::class)->create()->getKey();
        },
        'bake' => '{}'
    ];
});
