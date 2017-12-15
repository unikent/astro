<?php

use Astro\API\Models\RevisionSet;
use Astro\API\Models\Site;

$factory->define(RevisionSet::class, function($faker){return ['site_id' => factory(Site::class)];});
