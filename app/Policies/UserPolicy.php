<?php

namespace App\Policies;

use App\Policies\BasePolicy;
use App\Models\User;

class UserPolicy extends BasePolicy
{
    /**
     * Determine whether the user can list all users
     *
     * @param  User  $user
     * @return boolean
     */
    public function index(User $user)
    {
        return true;
    }

	/**
	 * Can the currently authenticated user view details about the specified user?
	 * @param User $current_user - Currently authenticated user
	 * @param User $target - Target user to test for access to.
	 * @return bool - True if the current user can view details of the user, otherwise false.
	 */
    public function view(User $current_user, User $target)
	{
		// for now let all users view other users
		return true;
	}

}
