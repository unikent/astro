<?php
namespace App\Http\Transformers\Api\v1;

use App\Models\Page;
use League\Fractal\ParamBag;
use League\Fractal\Resource\Item as FractalItem;
use League\Fractal\Resource\Collection as FractalCollection;
use League\Fractal\Resource\Item;
use League\Fractal\TransformerAbstract as FractalTransformer;
use App\Http\Transformers\Api\v1\Definitions\LayoutTransformer as LayoutDefinitionTransformer;

/**
 * Transforms Page from the database to the correct format for the API to output.
 * @package App\Http\Transformers\Api\v1
 */
class PageTransformer extends FractalTransformer
{

    protected $availableIncludes = [
    	'parent',
		'revision',
		'revisions',
		'site',
		'layout_definition',
		'ancestors',
		'children',
		'siblings',
		'next',
		'previous'
	];

    protected $full = true; // whether to include blocks with output

    /**
     * Create a PageTransformer.
     * @param bool $full Whether or not to include blocks with the output.
     */
    public function __construct($full = false)
    {
        $this->full = $full;
    }

    public function transform(Page $page)
	{
		$data = [
		    'id' => $page->id,
            'slug' => $page->slug,
            'path' => $page->path,
            'version' => $page->version,
            'title' => $page->revision->title,
            'layout' => [
                'name' => $page->revision->layout_name,
                'version' => $page->revision->layout_version
            ],
            'options' => $page->revision->options,
            'depth' => $page->depth,
            'parent_id' => $page->parent_id,
            'site_id' => $page->site_id,
            'revision_id' => $page->revision_id,
			'valid' => $page->revision->valid,
			'status' => $page->status
		];
		if($this->full){
            $data['blocks'] = $page->revision->blocks;
        }
        return $data;
	}

    /**
     * Include associated Parent
     * @param Page $page The Page whose parent to transform.
     * @return FractalItem
     */
    public function includeParent(Page $page, ParamBag $params = null)
    {
        if($page->parent) {
            return new FractalItem($page->parent, new PageTransformer($params->get('full')), false);
        }
    }

    /**
     * Include associated Revision
     * @param Page $page The Page to transform.
     * @return FractalItem
     */
    public function includeRevision(Page $page, ParamBag $params = null)
    {
        if($page->revision) {
            return new FractalItem($page->revision, new RevisionTransformer( $params->get('full') ), false);
        }
    }

    /**
     * Include associated Site
     * @return FractalItem
     */
    public function includeSite(Page $page)
    {
        if($page->site){
            return new FractalItem($page->site, new SiteTransformer($page->version), false);
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
        }else{
            return [];
        }
    }

    /**
     * Include associated Layout/Region definitions
     * @param Page $page
     * @return FractalItem
     */
    public function includeLayoutDefinition(Page $page)
    {
        $layoutDefinition = $page->getLayoutDefinition();
        return new FractalItem($layoutDefinition, new LayoutDefinitionTransformer, false);
    }

    /**
     * Include History (all Revisions up to and including the current one)
     * @param Page $page The page.
     * @return FractalCollection
     */
    public function includeRevisions(Page $page)
    {
        if(!$page->history->isEmpty()){
            return new FractalCollection($page->revision->history, new RevisionTransformer, false);
        }
    }

	/**
	 * Include direct ancestors of this Page, as an array, starting with home page.
	 * @param Page $page The page.
	 * @return FractalCollection
	 */
	public function includeAncestors(Page $page)
	{
		$ancestors = $page->ancestors()->with('revision')->orderBy('lft')->get();
		if($ancestors){
			return new FractalCollection($ancestors, new PageTransformer, false);
		}
	}

	/**
	 * Get the children of this page in order.
	 * @param Page $page
	 * @return FractalCollection
	 */
	public function includeChildren(Page $page)
	{
		$children = $page->children()->with('revision')->orderBy('lft')->get();
		if($children){
			return new FractalCollection($children, new PageTransformer, false);
		}
	}

	/**
	 * Get the siblings of this page in order.
	 * @param Page $page
	 * @return FractalCollection
	 */
	public function includeSiblings(Page $page)
	{
		$siblings = $page->siblingsAndSelf()->with('revision')->orderBy('lft')->get();
		if($siblings){
			return new FractalCollection($siblings, new PageTransformer, false);
		}
	}

	/**
	 * Get the "next" sibling page to this one
	 * @param Page $page
	 * @return Item
	 */
	public function includeNext(Page $page)
	{
		$sibling = $page->nextPage();
		if($sibling){
			return new Item($sibling, new PageTransformer, false);
		}
	}

	/**
	 * Get the "previous" sibling page to this one
	 * @param Page $page
	 * @return Item
	 */
	public function includePrevious(Page $page)
	{
		$sibling = $page->previousPage();
		if($sibling){
			return new Item($sibling, new PageTransformer, false);
		}
	}

}
