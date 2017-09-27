<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class PublishingGroupPolicy
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
     * Determine whether the user can index Site.
     *
     * @param  User  $user
     * @return boolean
     */
    public function index(User $user)
    {
        return false;
    }


}
