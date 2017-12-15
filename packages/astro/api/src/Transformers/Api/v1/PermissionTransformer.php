<?php
namespace Astro\API\Transformers\Api\v1;

use Astro\API\Models\Permission;
use League\Fractal\ParamBag;
use League\Fractal\Resource\Item;
use League\Fractal\TransformerAbstract as FractalTransformer;
use League\Fractal\Resource\Collection as FractalCollection;

class PermissionTransformer extends FractalTransformer
{
	protected $availableIncludes = ['roles'];

	public function transform(Permission $permission)
	{
		return [
			'name' => $permission->name,
			'slug' => $permission->slug
		];
	}

	public function includeRoles(Permission $permission, ParamBag $params = null)
	{
		if($permission->roles){
			if($params->get('full')) {
				return new FractalCollection($permission->roles, new RoleTransformer, false);
			}else{
				return new Item($permission->roles->pluck('slug')->toArray(), function($roles){return $roles;},false);
			}
		}
	}

}
