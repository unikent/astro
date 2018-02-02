<?php
namespace App\Http\Transformers\Api\v1;

use App\Models\Block;
use League\Fractal\Resource\Item as FractalItem;
use League\Fractal\TransformerAbstract as FractalTransformer;
use App\Http\Transformers\Api\v1\Definitions\BlockTransformer as BlockDefinitionTransformer;
use App\Http\Transformers\Api\v1\MediaTransformer;

class BlockTransformer extends FractalTransformer
{

    protected $availableIncludes = [ 'definition', 'media' ];

	public function transform(Block $block)
	{
        // embed media data if it's in our includes
        if($this->getCurrentScope()->isRequested('media')) {
            $block->embedMedia();
        }

		return $block->toArray();
	}

    /**
     * Include associated Layout/Region definitions
     */
    public function includeDefinition(Block $block)
    {
        $definition = $block->getDefinition();
        return new FractalItem($definition, new BlockDefinitionTransformer, false);
    }

    /**
     * Include associated media item(s).
     *
     * @return League\Fractal\ItemResource
     */
    public function includeMedia(Block $block) {}
    // block::with('media')->get() to eager-load

}
