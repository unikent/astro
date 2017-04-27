<?php

$factory->define(App\Models\Site::class, function (Faker\Generator $faker) {
	return [
		'name' => $faker->name,
	];
});

$factory->state(App\Models\Site::class, 'withPublishingGroup', function ($faker) {
    $group = factory(App\Models\PublishingGroup::class)->create();

    return [
        'publishing_group_id' => $group->getKey(),
    ];
});
