<?php
namespace App\Http\Transformers\Api\v1;

use League\Fractal\TransformerAbstract as FractalTransformer;
use League\Fractal\Resource\Collection as FractalCollection;
use App\Models\UserSiteRole;

class UserRoleTransformer extends FractalTransformer
{
	public function transform(UserSiteRole $role)
	{
		$data = [
		  'site' => [
		  		'id' => $role->site->id,
			  	'name' => $role->site->name,
			  	'host' => $role->site->host,
			  	'path' => $role->site->path
			  ],
			'role' => $role->role->name
        ];
		return $data;
	}
}
