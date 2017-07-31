<?php
namespace App\Http\Transformers\Api\v1;

use App\Models\Site;
use App\Models\Page;
use League\Fractal\Resource\Collection;
use League\Fractal\Resource\Item as FractalItem;
use League\Fractal\Resource\Collection as FractalCollection;
use League\Fractal\TransformerAbstract as FractalTransformer;

class SiteTransformer extends FractalTransformer
{

    protected $defaultIncludes = [ ];
    protected $availableIncludes = [ 'drafts,pages,published' ];

	public function transform(Site $site)
	{
		return $site->toArray();
	}

    public function includeDrafts(Site $site)
    {
        if(!$site->draftPages->isEmpty()){
            return new FractalCollection($site->draftPages, new PageTransformer, false);
        }
    }

    public function includePages(Site $site)
    {
        if(!$site->pages->isEmpty()){
            return new FractalCollection($site->pages, new PageTransformer, false);
        }
    }

    /**
     * Include all associated Routes
     *
     * @return Collection
     */
    public function includePublished(Site $site)
    {
        if(!$site->publishedPages->isEmpty()){
            return new FractalCollection($site->publishedPages, new PageTransformer, false);
        }
    }

}
