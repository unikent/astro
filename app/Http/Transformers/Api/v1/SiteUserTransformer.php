<?php
namespace App\Http\Transformers\Api\v1;

use League\Fractal\TransformerAbstract as FractalTransformer;
use League\Fractal\Resource\Collection as FractalCollection;
use App\Models\UserSiteRole;

class SiteUserTransformer extends FractalTransformer
{
	public function transform(UserSiteRole $role)
	{
		$data = [
	  		'username' => $role->user->username,
		  	'name' => $role->user->name,
		  	'email' => $role->user->email,
		  	'global_role' => $role->user->role,
			'role' => $role->role->name
        ];
		return $data;
	}
}
