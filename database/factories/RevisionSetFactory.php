<?php

use App\Models\RevisionSet;
use App\Models\Site;

$factory->define(RevisionSet::class, function($faker){return ['site_id' => factory(Site::class)];});
