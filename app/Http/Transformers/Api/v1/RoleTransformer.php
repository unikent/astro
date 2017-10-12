<?php
namespace App\Http\Transformers\Api\v1;

use League\Fractal\TransformerAbstract as FractalTransformer;
use App\Models\Role;

class RoleTransformer extends FractalTransformer
{
	public function transform(Role $role)
	{
		return [
			'name' => $role->name
		];
	}
}
