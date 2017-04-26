<?php
namespace App\Http\Transformers\Api\v1\Definitions;

use App\Models\Definitions\Region;
use League\Fractal\Resource\Collection as FractalCollection;
use League\Fractal\TransformerAbstract as FractalTransformer;

class RegionTransformer extends FractalTransformer
{
    protected $availableIncludes = [ 'block_definitions' ];

	public function transform(Region $region)
	{
		return $region->toArray();
	}

    /**
     * Include associated Block definitions
     *
     * @return League\Fractal\ItemResource
     */
    public function includeBlockDefinitions(Region $region)
    {
    	$definitions = $region->getBlockDefinitions();
    	return new FractalCollection($definitions, new BlockTransformer, false);
    }

}
