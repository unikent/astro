<?php
namespace App\Models\Definitions;


class Block extends BaseDefinition
{

    protected static $defDir = 'blocks';

	protected $attributes = [
		'name',
		'label',
		'version',
		'fields',
	];

	protected $casts = [
        'fields' => 'array',
	];

}
