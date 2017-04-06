<?php
namespace App\Models\Definitions;


class Block extends BaseDefinition
{

    protected static $defDir = 'blocks';

	protected $casts = [
        'fields' => 'array',
	];

}
