<?php
namespace App\Http\Transformers\Api\v1;

use App\Models\Route;
use League\Fractal\Resource\Item as FractalItem;
use League\Fractal\TransformerAbstract as FractalTransformer;

class RouteTransformer extends FractalTransformer
{

    protected $availableIncludes = [ 'parent', 'page' ];

	public function transform(Route $route)
	{
		return $route->toArray();
	}

   /**
     * Include associated Parent
     * @return League\Fractal\ItemResource
     */
    public function includeParent(Route $route)
    {
        return new FractalCollection($route->parent, new RouteTransformer, false);
    }

    /**
     * Include associated Page
     * @return League\Fractal\ItemResource
     */
    public function includePage(Route $route)
    {
    	return new FractalCollection($route->page, new PageTransformer, false);
    }

}
