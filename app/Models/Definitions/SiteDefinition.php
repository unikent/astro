<?php
namespace App\Models\Definitions;

use Illuminate\Support\Collection;

class SiteDefinition extends BaseDefinition
{

	public static $defDir = 'sites';

	protected $casts = [
        'allowedLayouts' => 'array'
	];
}
