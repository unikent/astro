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
    protected $availableIncludes = [ 'drafts','pages','published','publishing_group' ];

	public function transform(Site $site)
	{
		$data = [
		  'id' => $site->id,
            'name' => $site->name,
            'host' => $site->host,
            'path' => $site->path,
            'created_at' => $site->created_at,
            'updated_at' => $site->updated_at,
            'deleted_at' => $site->deleted_at
        ];
		return $data;
	}

	public function includePublishingGroup(Site $site)
    {
        if($site->publishing_group){
            return new FractalItem($site->publishing_group, new PublishingGroupTransformer, false);
        }
    }

    public function includeDrafts(Site $site)
    {
        $drafts = $site->draftPages;
        if($drafts){
            $drafts->load('draft');
            return new FractalCollection($drafts, new PageTransformer(PageTransformer::DRAFT_PAGES), false);
        }
    }

    public function includePages(Site $site)
    {
        if(!$site->pages->isEmpty()){
            return new FractalCollection($site->pages, new PageTransformer(PageTransformer::ALL_PAGES), false);
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
            return new FractalCollection($site->publishedPages, new PageTransformer(PageTransformer::PUBLISHED_PAGES), false);
        }
    }

}
