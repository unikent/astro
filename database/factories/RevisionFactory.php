<?php

use App\Models\Revision;
use App\Models\RevisionSet;

$factory->define(Revision::class, function($faker){
    return [
        'revision_set_id' => function($revision) {
            return factory(RevisionSet::class)->create()->getKey();
        },
        'bake' => '{}'
    ];
});
