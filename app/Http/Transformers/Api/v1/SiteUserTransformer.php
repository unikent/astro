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
		  'user' => [
		  		'username' => $role->user->username,
			  	'name' => $role->user->name,
			  	'email' => $role->user->email,
			  	'role' => $role->user->role // should be changed to is_admin or something
			  ],
			'role' => $role->role->name
        ];
		return $data;
	}
}
