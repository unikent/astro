<?php
namespace App\Http\Transformers\Api\v1;

use App\Models\Route;
use League\Fractal\Resource\Item as FractalItem;
use League\Fractal\Resource\Collection as FractalCollection;
use League\Fractal\TransformerAbstract as FractalTransformer;

class RouteTransformer extends FractalTransformer
{

    protected $availableIncludes = [ 'parent', 'page', 'site' ];

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
        return new FractalItem($route->parent, new RouteTransformer, false);
    }

    /**
     * Include associated Page
     * @return League\Fractal\ItemResource
     */
    public function includePage(Route $route)
    {
        return new FractalItem($route->page, new PageTransformer, false);
    }

    /**
     * Include associated Site
     * @return League\Fractal\ItemResource
     */
    public function includeSite(Route $route)
    {
        if($route->site){
            return new FractalItem($route->site, new SiteTransformer, false);
        }
    }

}
