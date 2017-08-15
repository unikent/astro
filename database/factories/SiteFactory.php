<?php

use App\Models\Site;
use App\Models\PublishingGroup;
use Faker\Generator;

$factory->define(Site::class, function ($faker) {
    $group = factory(PublishingGroup::class)->create();
	return [
		'name' => $faker->sentence(2),
        'host' => $faker->domainName(),
        'path' => '',
        'publishing_group_id' => $group->id,
        '' => json_encode(['layout_name' => 'kent-homepage', 'layout_version' => 1])
	];
});

$factory->state(Site::class, 'withPublishingGroup', function ($faker) {
    return [
    ];
});