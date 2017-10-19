<?php
namespace App\Http\Transformers\Api\v1;

use League\Fractal\ParamBag;
use League\Fractal\Resource\Item;
use League\Fractal\TransformerAbstract as FractalTransformer;
use League\Fractal\Resource\Collection as FractalCollection;
use App\Models\Role;

class RoleTransformer extends FractalTransformer
{
	protected $availableIncludes = ['permissions'];

	public function transform(Role $role)
	{
		return [
			'name' => $role->name,
			'slug' => $role->slug
		];
	}

	public function includePermissions(Role $role, ParamBag $params = null)
	{
		if($role->permissions){
			if($params->get('full')) {
				return new FractalCollection($role->permissions, new PermissionTransformer, false);
			}else{
				return new Item($role->permissions->pluck('slug')->toArray(), function($permissions){ return $permissions;},false);
			}
		}
	}

}
