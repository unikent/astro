<?php
namespace Astro\API\Transformers\Api\v1\Definitions;

use Astro\API\Models\Definitions\Layout;
use League\Fractal\Resource\Collection as FractalCollection;
use League\Fractal\TransformerAbstract as FractalTransformer;

class LayoutTransformer extends FractalTransformer
{

    protected $availableIncludes = [ 'region_definitions' ];

	public function transform(Layout $layout)
	{
		return $layout->toArray();
	}

    /**
     * Include associated Region definitions
     *

     */
    public function includeRegionDefinitions(Layout $layout)
    {
    	$definitions = $layout->getRegionDefinitions();
    	return new FractalCollection($definitions, new RegionTransformer, false);
    }

}
