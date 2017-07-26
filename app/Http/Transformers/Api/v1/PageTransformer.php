<?php
namespace App\Http\Transformers\Api\v1;

use App\Models\Page;
use League\Fractal\Resource\Item as FractalItem;
use League\Fractal\Resource\Collection as FractalCollection;
use League\Fractal\TransformerAbstract as FractalTransformer;

class PageTransformer extends FractalTransformer
{

    protected $availableIncludes = [ 'parent', 'page', 'site' ];

	public function transform(Page $page)
	{
		return $page->toArray();
	}

    /**
     * Include associated Parent
     * @return League\Fractal\ItemResource
     */
    public function includeParent(Page $page)
    {
        return new FractalItem($page->parent, new PageTransformer, false);
    }

    /**
     * Include associated PageContent
     * @return League\Fractal\ItemResource
     */
    public function includePage(Page $page)
    {
        return new FractalItem($page->page, new PageContentTransformer, false);
    }

    /**
     * Include associated Site
     * @return League\Fractal\ItemResource
     */
    public function includeSite(Page $page)
    {
        if($page->site){
            return new FractalItem($page->site, new SiteTransformer, false);
        }
    }

}
