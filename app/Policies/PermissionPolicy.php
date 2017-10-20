<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class PermissionPolicy
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
     * Determine whether the user can list all permissions
     *
     * @param  User  $user
     * @return boolean
     */
    public function list(User $user)
    {
        return false;
    }


}
