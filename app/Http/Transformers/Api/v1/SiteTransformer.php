<?php
namespace App\Http\Transformers\Api\v1;

use App\Models\Site;
use ArrayObject;
use League\Fractal\ParamBag;
use League\Fractal\Resource\Item as FractalItem;
use League\Fractal\Resource\Collection as FractalCollection;
use League\Fractal\TransformerAbstract as FractalTransformer;

class SiteTransformer extends FractalTransformer
{

    protected $defaultIncludes = [ ];
    protected $availableIncludes = [ 'pages','publishing_group','homepage','role', 'users' ];

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
			'options' => $site->options ? $site->options : new ArrayObject
        ];
		return $data;
	}

	public function includeRole(Site $site)
	{
		if($site->currentUserRole){
			return new FractalItem($site->currentUserRole->role, new RoleTransformer(), false);
		}
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
}
