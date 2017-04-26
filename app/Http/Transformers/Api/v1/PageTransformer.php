<?php
namespace App\Http\Transformers\Api\v1;

use App\Models\Page;
use League\Fractal\Resource\Item as FractalItem;
use League\Fractal\Resource\Collection as FractalCollection;
use League\Fractal\TransformerAbstract as FractalTransformer;
use App\Http\Transformers\Api\v1\Definitions\LayoutTransformer as LayoutDefinitionTransformer;

class PageTransformer extends FractalTransformer
{

	protected $defaultIncludes = [ 'canonical' ];
    protected $availableIncludes = [ 'routes', 'blocks', 'layout_definition' ];

	public function transform(Page $page)
	{
		return $page->toArray();
	}

    /**
     * Include associated 'Canonical' Route
     *
     * @return League\Fractal\ItemResource
     */
    public function includeCanonical(Page $page)
    {
    	if($page->canonical){
	    	return new FractalItem($page->canonical, new RouteTransformer, false);
    	}
    }

    /**
     * Include all associated Routes
     *
     * @return League\Fractal\ItemResource
     */
    public function includeRoutes(Page $page)
    {
        if(!$page->routes->isEmpty()){
            return new FractalCollection($page->routes, new RouteTransformer, false);
        }
    }

    /**
     * Include associated Blocks
     *
     * @return League\Fractal\ItemResource
     */
    public function includeBlocks(Page $page)
    {
    	if(!$page->blocks->isEmpty()){
	    	return new FractalCollection($page->blocks, new BlockTransformer, false);
    	}
    }

    /**
     * Include associated Layout/Region definitions
     * @return League\Fractal\ItemResource
     */
    public function includeLayoutDefinition(Page $page)
    {
    	$layoutDefinition = $page->getLayoutDefinition();
    	return new FractalItem($layoutDefinition, new LayoutDefinitionTransformer, false);
    }


}
