<?php

use App\Models\Site;

$factory->define(Site::class, function ($faker) {
	return [
		'name' => $faker->sentence(2),
        'host' => $faker->domainWord . '-' . $faker->domainWord . '-' . $faker->domainWord . '-' . $faker->tld,
        'path' => '',
        'options' => []
	];
});

