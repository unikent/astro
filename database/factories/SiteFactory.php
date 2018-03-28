<?php

use App\Models\Site;

$factory->define(Site::class, function ($faker) {
	$host = $faker->domainName();
	while (Site::where('host', $host)->get()) {
		$host = $faker->domainName();
	}
	return [
		'name' => $faker->sentence(2),
        'host' => $host,
        'path' => '',
        'options' => []
	];
});

