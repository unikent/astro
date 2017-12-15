<?php
namespace Tests\Support\Doubles\Models\Definitions;

use Astro\API\Models\Definitions\BaseDefinition;

class Double extends BaseDefinition {

	protected $attributes = [ 'foo', 'bar', 'fields' ];

	protected $fillable = [ 'foo' ];

}
