<?php
namespace App\Http\Transformers\Api\v1;

use League\Fractal\TransformerAbstract as FractalTransformer;
use App\Models\User;

class UserTransformer extends FractalTransformer
{
	public function transform(User $user)
	{
		$data = [
		  'id' => $user->id,
            'name' => $user->name,
            'username' => $user->username,
            'email' => $user->email,
        ];
		return $data;
	}
}
