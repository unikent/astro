<?php
namespace Astro\API\Transformers\Api\v1;

use League\Fractal\TransformerAbstract as FractalTransformer;
use League\Fractal\Resource\Collection as FractalCollection;
use App\Models\User;

class UserTransformer extends FractalTransformer
{
	protected $availableIncludes = [ 'roles' ];

	public function transform(User $user)
	{
		$data = [
		  'id' => $user->id,
            'name' => $user->name,
            'username' => $user->username,
            'email' => $user->email,
            'global_role' => $user->role,
        ];
		return $data;
	}

	public function includeRoles(User $user)
	{
		// eager load relations if not already loaded...
		if(!$user->relationLoaded('roles')) {
			$roles = $user->roles()->with('site','role')->get();
		}else{
			$roles = $user->roles;
		}
		if(!$roles->isEmpty()){
			return new FractalCollection($roles, new UserRoleTransformer(),false);
		}
	}

}
