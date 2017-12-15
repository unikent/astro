<?php
namespace Astro\API\Transformers\Api\v1\Definitions;

use Astro\API\Models\Definitions\Block;
use League\Fractal\Resource\Collection as FractalCollection;
use League\Fractal\TransformerAbstract as FractalTransformer;

class BlockTransformer extends FractalTransformer
{

	public function transform(Block $definition)
	{
		return $definition->toArray();
	}

}
