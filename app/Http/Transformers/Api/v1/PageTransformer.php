<?php
namespace App\Http\Transformers\Api\v1;

use App\Models\Page;
use Illuminate\Database\Eloquent\Collection;
use League\Fractal\Resource\Item as FractalItem;
use League\Fractal\Resource\Collection as FractalCollection;
use League\Fractal\TransformerAbstract as FractalTransformer;
use App\Http\Transformers\Api\v1\Definitions\LayoutTransformer as LayoutDefinitionTransformer;

class PageTransformer extends FractalTransformer
{

	protected $defaultIncludes = [ 'active_route' ];
    protected $availableIncludes = [ 'routes', 'draft_route', 'blocks', 'layout_definition', 'published', 'history' ];

	public function transform(Page $page)
	{
		return $page->toArray();
	}

    /**
     * Include associated active Route
     *
     * @return League\Fractal\ItemResource
     */
    public function includeActiveRoute(Page $page)
    {
    	if($page->activeRoute){
	    	return new FractalItem($page->activeRoute, new RouteTransformer, false);
    	}
    }

    /**
     * Include associated draft Route
     *
     * @return League\Fractal\ItemResource
     */
    public function includeDraftRoute(Page $page)
    {
        if($page->draftRoute){
            return new FractalItem($page->draftRoute, new RouteTransformer, false);
        }
    }

    /**
     * Include all associated Routes
     *
     * @return League\Fractal\CollectionResource
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
     * It was decided that API clients would rather consume ordered blocks sorted
     * into regions, rather than duplicating ordering and grouping logic in every client.
     *
     * Some nastiness resides here in order to achieve this, commented below...
     *
     * @return League\Fractal\CollectionResource
     */
    public function includeBlocks(Page $page)
    {
    	if(!$page->blocks->isEmpty()){
            // Using sortBy instead of orderBy as the collection might have been eager-loaded
            // Use of groupBy results in a Collection with nested Collections, keyed by 'region_name'.
            $blocksByRegion = $page->blocks->sortBy('order')->groupBy('region_name');

            // Unfortunately Fractal cannot serialize a Collection with nested Collections. We use an
            // inline Transformer to create a FractalItem, serializing nested FractalCollections as we go.
            // We use the current scope to access the manager and also pass it to createData to ensure
            // includes continue to function.
            $scope = $this->getCurrentScope();

            return new FractalItem($blocksByRegion, function(Collection $blocksByRegion) use ($scope){
                foreach($blocksByRegion as $region => $blocks){
                    $collection = new FractalCollection($blocks, new BlockTransformer, false);
                    $blocksByRegion[$region] = $scope->getManager()->createData($collection, 'blocks', $scope)->toArray();
                }

                return $blocksByRegion->toArray();
            }, false);
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

    /**
     * Include Published (latest PublishedPage)
     *
     * @return League\Fractal\ItemResource
     */
    public function includePublished(Page $page)
    {
        if($page->published){
            return new FractalItem($page->published, new PublishedPageTransformer, false);
        }
    }

    /**
     * Include History (all associated PublishedPages)
     *
     * @return League\Fractal\CollectionResource
     */
    public function includeHistory(Page $page)
    {
        if(!$page->history->isEmpty()){
            return new FractalCollection($page->history, new PublishedPageTransformer, false);
        }
    }

}
