<?php
namespace App\Http\Transformers\Api\v1;

use App\Models\Site;
use App\Models\Route;
use League\Fractal\Resource\Item as FractalItem;
use League\Fractal\Resource\Collection as FractalCollection;
use League\Fractal\TransformerAbstract as FractalTransformer;

class SiteTransformer extends FractalTransformer
{

    protected $defaultIncludes = [ 'active_route' ];
    protected $availableIncludes = [ 'routes' ];

	public function transform(Site $site)
	{
		return $site->toArray();
	}

    /**
     * Include associated 'Canonical' Route
     *
     * @return League\Fractal\ItemResource
     */
    public function includeActiveRoute(Site $site)
    {
        if($site->activeRoute){
            return new FractalItem($site->activeRoute, new RouteTransformer, false);
        }
    }

    /**
     * Include all associated Routes
     *
     * @return League\Fractal\ItemResource
     */
    public function includeRoutes(Site $site)
    {
        if(!$site->routes->isEmpty()){
            return new FractalCollection($site->routes, new RouteTransformer, false);
        }
    }

}
