<?php
namespace App\Http\Transformers\Api\v1;

use App\Models\Site;
use App\Models\Page;
use League\Fractal\ParamBag;
use League\Fractal\Resource\Collection;
use League\Fractal\Resource\Item as FractalItem;
use League\Fractal\Resource\Collection as FractalCollection;
use League\Fractal\TransformerAbstract as FractalTransformer;

class SiteTransformer extends FractalTransformer
{

    protected $defaultIncludes = [ ];
    protected $availableIncludes = [ 'pages','publishing_group','homepage' ];

	public function transform(Site $site)
	{
		$data = [
		  'id' => $site->id,
            'name' => $site->name,
            'host' => $site->host,
            'path' => $site->path,
            'created_at' => $site->created_at ? $site->created_at->__toString() : null,
            'updated_at' => $site->updated_at ? $site->updated_at->__toString() : null,
            'deleted_at' => $site->deleted_at ? $site->deleted_at->__toString() : null
        ];
		return $data;
	}

	public function includeHomepage(Site $site, ParamBag $params = null)
    {
        if($site->homepage){
            return new FractalItem($site->homepage, new PageTransformer($params->get('full')), false);
        }
    }

	public function includePublishingGroup(Site $site)
    {
        if($site->publishing_group){
            return new FractalItem($site->publishing_group, new PublishingGroupTransformer, false);
        }
    }

    public function includePages(Site $site)
    {
        if(!$site->pages->isEmpty()){
            return new FractalCollection($site->pages, new PageTransformer(), false);
        }
    }
}
