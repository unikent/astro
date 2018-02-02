<?php

namespace App\Policies;

use App\Policies\BasePolicy;
use App\Models\User;

class RolePolicy extends BasePolicy
{
    /**
     * Determine whether the user can list all roles
     *
     * @param  User  $user
     * @return boolean
     */
    public function list(User $user)
    {
        return true;
    }


}
