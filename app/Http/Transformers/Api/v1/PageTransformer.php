<?php
namespace App\Http\Transformers\Api\v1;

use App\Models\Page;
use League\Fractal\ParamBag;
use League\Fractal\Resource\Item as FractalItem;
use League\Fractal\Resource\Collection as FractalCollection;
use League\Fractal\Serializer\ArraySerializer;
use League\Fractal\TransformerAbstract as FractalTransformer;

/**
 * Transforms Page from the database to the correct format for the API to output.
 * @package App\Http\Transformers\Api\v1
 */
class PageTransformer extends FractalTransformer
{

    protected $availableIncludes = [ 'parent', 'draft', 'published', 'site' ];

    const ALL_PAGES = 1;
    const DRAFT_PAGES = 2;
    const PUBLISHED_PAGES = 4;

    /**
     * Create a PageTransformer to filter specific page types.
     * @param int $filter
     */
    public function __construct($filter = self::ALL_PAGES)
    {
        if(!in_array($filter,[self::ALL_PAGES,self::DRAFT_PAGES,self::PUBLISHED_PAGES])){
            throw new \InvalidArgumentException("No valid filter specified.");
        }
        $this->filter = $filter;
    }

	public function transform(Page $page)
	{
		$data = [
		    'id' => $page->id,
            'path' => $page->path,
            'slug' => $page->slug,
            'state' => $page->draftState(),
            'depth' => $page->depth,
            'parent_id' => $page->parent_id
        ];
        return $data;
	}

    /**
     * Include associated Parent
     * @param Page $page The Page whose parent to transform.
     * @return FractalItem
     */
    public function includeParent(Page $page)
    {
        if($page->parent) {
            return new FractalItem($page->parent, new PageTransformer, false);
        }
    }

    /**
     * Include associated PageContent
     * @param Page $page The Page to transform.
     * @return FractalItem
     */
    public function includeDraft(Page $page, ParamBag $params = null)
    {
        if($page->draft) {
            return new FractalItem($page->draft, new RevisionTransformer( $params->get('full') ), false);
        }
    }

    /**
     * Include associated PageContent
     * @param Page $page The Page to transform.
     * @return FractalItem
     */
    public function includePublished(Page $page, ParamBag $params = null)
    {
        if($page->published) {
            return new FractalItem($page->published, new RevisionTransformer( $params->get('full') ), false);
        }
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
