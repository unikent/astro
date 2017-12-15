<?php
namespace Astro\API\Transformers\Api\v1;

use Astro\API\Models\Page;
use Astro\API\Models\Site;
use ArrayObject;
use League\Fractal\ParamBag;
use League\Fractal\Resource\Item as FractalItem;
use League\Fractal\Resource\Collection as FractalCollection;
use League\Fractal\Resource\Item;
use League\Fractal\TransformerAbstract as FractalTransformer;

/**
 * Transforms a Site into json.
 * Needs to know which "version" (draft, published) of the site is being requested so it knows which version
 * of pages to optionally include.
 * @package Astro\API\Transformers\Api\v1
 */
class SiteTransformer extends FractalTransformer
{

    protected $defaultIncludes = [ ];
    protected $availableIncludes = [
		'draft_homepage',
		'published_homepage',
		'draft_pages',
		'published_pages',
		'users',
		'homepage', // draft or published determined by $this->version
		'pages', // draft or published determined by $this->version
	];

	/**
	 * The "version" of the site to retrieve pages for (draft, published)
	 * @var null|string
	 */
    public $version = Page::STATE_DRAFT;

	/**
	 * The "version" of the site to retrieve pages for (draft, published)
	 * @var null|string
	 */
    public function __construct($version = null)
	{
		if($version){
			$this->version = $version;
		}
	}

	public function transform(Site $site)
	{
		$data = [
		  'id' => $site->id,
            'name' => $site->name,
            'host' => $site->host,
            'path' => $site->path,
            'created_at' => $site->created_at ? $site->created_at->__toString() : null,
            'updated_at' => $site->updated_at ? $site->updated_at->__toString() : null,
            'deleted_at' => $site->deleted_at ? $site->deleted_at->__toString() : null,
			'options' => $site->options ? $site->options : new ArrayObject,
			'site_definition_name' => $site->site_definition_name,
			'site_definition_version' => $site->site_definition_version
        ];
		return $data;
	}

	/**
	 * Include the draft homepage of this site.
	 * @param Site $site
	 * @param ParamBag $params
	 * @return Item
	 */
	public function includeDraftHomepage(Site $site, ParamBag $params)
	{
		$homepage = $site->draftHomepage;
		if($homepage){
			return new FractalItem($homepage, new PageTransformer($params->get('full')), false);
		}
	}

	/**
	 * Include the published homepage of this site.
	 * @param Site $site
	 * @param ParamBag $params
	 * @return FractalItem
	 */
	public function includePublishedHomepage(Site $site, ParamBag $params)
	{
		$homepage = $site->publishedHomepage;
		if($homepage){
			return new FractalItem($homepage, new PageTransformer($params->get('full')), false);
		}
	}

	/**
	 * Include the pages that comprise the draft version of this site.
	 * @param Site $site
	 * @param ParamBag $params
	 * @return FractalCollection
	 */
	public function includeDraftPages(Site $site, ParamBag $params)
	{
		$pages = $site->draftPages()
			->orderBy('lft')
			->get();
		if($pages){
			return new FractalCollection($pages, new PageTransformer(), false);
		}
	}

	/**
	 * Include the pages that comprise the published version of this site.
	 * @param Site $site
	 * @param ParamBag $params
	 * @return FractalCollection
	 */
	public function includePublishedPages(Site $site, ParamBag $params)
	{
		$pages = $site->publishedPages()
			->orderBy('lft')
			->get();
		if($pages){
			return new FractalCollection($pages, new PageTransformer(), false);
		}
	}

	/**
	 * Include the users and roles for this Site
	 * @param Site $site
	 */
	public function includeUsers(Site $site)
	{
		if(!$site->usersRoles->isEmpty()){
			return new FractalCollection($site->usersRoles, new SiteUserTransformer(), false);
		}
	}

	/**
	 * @param Site $site
	 * @param ParamBag $params
	 * @return FractalCollection
	 */
	public function includePages(Site $site, ParamBag $params)
	{
		$pages = $site->pages($this->version)
			->orderBy('lft')
			->get();
		if($pages){
			return new FractalCollection($pages, new PageTransformer(), false);
		}
	}

	/**
	 * @param Site $site
	 * @param ParamBag $params
	 * @return FractalItem
	 */
	public function includeHomepage(Site $site, ParamBag $params)
	{
		$homepage = $site->homepage($this->version)->first();
		if($homepage){
			return new FractalItem($homepage, new PageTransformer($params->get('full')), false);
		}
	}


}
