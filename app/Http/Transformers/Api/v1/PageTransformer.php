<?php
namespace App\Http\Transformers\Api\v1;

use App\Models\Page;
use League\Fractal\Resource\Item as FractalItem;
use League\Fractal\TransformerAbstract as FractalTransformer;

/**
 * Transforms Page from the database to the correct format for the API to output.
 * @package App\Http\Transformers\Api\v1
 */
class PageTransformer extends FractalTransformer
{

    protected $availableIncludes = [ 'parent', 'draft', 'published', 'site' ];

	public function transform(Page $page)
	{
		return $page->toArray();
	}

    /**
    /**
     * Include associated Parent
     * @param Page $page The Page whose parent to transform.
     * @return FractalItem
     */
    public function includeParent(Page $page)
    {
        return new FractalItem($page->parent, new PageTransformer, false);
    }

    /**
     * Include associated PageContent
     * @param Page $page The Page to transform.
     * @return FractalItem
     */
    public function includePage(Page $page)
    {
        return new FractalItem($page->draft, new PageContentTransformer, false);
    }

    /**
     * Include associated Site
     * @return FractalItem
     */
    public function includeSite(Page $page)
    {
        if($page->site){
            return new FractalItem($page->site, new SiteTransformer, false);
        }
    }

}
