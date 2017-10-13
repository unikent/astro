<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class UserPolicy
{
    use HandlesAuthorization;

    public function before($user, $ability)
    {
        if($user->isAdmin()){
            return true;
        }
        return true;
    }

    /**
     * Determine whether the user can list all users
     *
     * @param  User  $user
     * @return boolean
     */
    public function index(User $user)
    {
        return false;
    }

	/**
	 * Can the currently authenticated user view details about the specified user?
	 * @param User $current_user - Currently authenticated user
	 * @param User $target - Target user to test for access to.
	 * @return bool - True if the current user can view details of the user, otherwise false.
	 */
    public function view(User $current_user, User $target)
	{
		// want to allow current user to view themselves
		return ($current_user->id == $target->id);
	}

}
