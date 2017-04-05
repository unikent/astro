<?php
namespace App\Models\Definitions;


class Block extends BaseDefinition
{

	protected $attributes = [
		'name',
		'type',
		'fields',
	];

	protected $casts = [
        'fields' => 'array',
	];

}
