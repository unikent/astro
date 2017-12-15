<?php
namespace Astro\API\Transformers\Api\v1;

use League\Fractal\TransformerAbstract as FractalTransformer;
use League\Fractal\Resource\Collection as FractalCollection;
use Astro\API\Models\UserSiteRole;

class UserRoleTransformer extends FractalTransformer
{
	public function transform(UserSiteRole $role)
	{
		$data = [
			'id' => $role->site->id,
			'name' => $role->site->name,
			'host' => $role->site->host,
			'path' => $role->site->path,
			'role' => $role->role->slug
        ];
		return $data;
	}
}
