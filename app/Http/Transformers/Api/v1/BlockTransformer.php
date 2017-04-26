<?php
namespace App\Http\Transformers\Api\v1;

use App\Models\Block;
use League\Fractal\Resource\Item as FractalItem;
use League\Fractal\TransformerAbstract as FractalTransformer;
use App\Http\Transformers\Api\v1\Definitions\BlockTransformer as BlockDefinitionTransformer;

class BlockTransformer extends FractalTransformer
{

    protected $availableIncludes = [ 'definition' ];

	public function transform(Block $block)
	{
		return $block->toArray();
	}

    /**
     * Include associated Layout/Region definitions
     * @return League\Fractal\ItemResource
     */
    public function includeDefinition(Block $block)
    {
        $definition = $block->getDefinition();
        return new FractalItem($definition, new BlockDefinitionTransformer, false);
    }

}
