<?php
namespace App\Http\Transformers\Api\v1;

use App\Models\PageContent;
use Illuminate\Database\Eloquent\Collection;
use League\Fractal\Resource\Item as FractalItem;
use League\Fractal\Resource\Collection as FractalCollection;
use League\Fractal\TransformerAbstract as FractalTransformer;
use App\Http\Transformers\Api\v1\Definitions\LayoutTransformer as LayoutDefinitionTransformer;

class PageContentTransformer extends FractalTransformer
{

	protected $defaultIncludes = [ 'active_route' ];
    protected $availableIncludes = [ 'routes', 'draft_route', 'blocks', 'layout_definition', 'published', 'history' ];

	public function transform(PageContent $pagecontent)
	{
		return $pagecontent->toArray();
	}

    /**
     * Include associated active Route
     * @param PageContent $pagecontent
     * @return FractalItem
     */
    public function includeActiveRoute(PageContent $pagecontent)
    {
    	if($pagecontent->activeRoute){
	    	return new FractalItem($pagecontent->activeRoute, new PageTransformer, false);
    	}
    }

    /**
     * Include associated draft Route
     *
     * @return FractalItem
     */
    public function includeDraftRoute(PageContent $pagecontent)
    {
        if($pagecontent->draftRoute){
            return new FractalItem($pagecontent->draftRoute, new PageTransformer, false);
        }
    }

    /**
     * Include all associated Routes
     * @param PageContent $pagecontent
     * @return FractalCollection
     */
    public function includeRoutes(PageContent $pagecontent)
    {
        if(!$pagecontent->routes->isEmpty()){
            return new FractalCollection($pagecontent->routes, new PageTransformer, false);
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
     */
    public function includeBlocks(PageContent $pagecontent)
    {
    	if(!$pagecontent->blocks->isEmpty()){
            // Using sortBy instead of orderBy as the collection might have been eager-loaded
            // Use of groupBy results in a Collection with nested Collections, keyed by 'region_name'.
            $blocksByRegion = $pagecontent->blocks->sortBy('order')->groupBy('region_name');

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
     * @param PageContent $pagecontent
     * @return FractalItem
     */
    public function includeLayoutDefinition(PageContent $pagecontent)
    {
    	$layoutDefinition = $pagecontent->getLayoutDefinition();
    	return new FractalItem($layoutDefinition, new LayoutDefinitionTransformer, false);
    }

    /**
     * Include Published (latest PublishedPage)
     * @param PageContent $pagecontent
     * @return FractalItem
     */
    public function includePublished(PageContent $pagecontent)
    {
        if($pagecontent->published){
            return new FractalItem($pagecontent->published, new RevisionTransformer, false);
        }
    }

    /**
     * Include History (all associated PublishedPages)
     * @param PageContent $pagecontent
     * @return FractalCollection
     */
    public function includeHistory(PageContent $pagecontent)
    {
        if(!$pagecontent->history->isEmpty()){
            return new FractalCollection($pagecontent->history, new RevisionTransformer, false);
        }
    }

}
