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


}
