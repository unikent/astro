<?php
namespace App\Models\Definitions;


class Block extends BaseDefinition
{

	public static $defDir = 'blocks';

	protected $casts = [
        'fields' => 'array',
	];

}
