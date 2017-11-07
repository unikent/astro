<?php

namespace App\Policies;

use Illuminate\Auth\Access\HandlesAuthorization;

use App\Models\User;

class BasePolicy
{
	use HandlesAuthorization;

	public function before(User $user)
	{
		if($user->isAdmin()) {
			return true;
		}
	}

}
