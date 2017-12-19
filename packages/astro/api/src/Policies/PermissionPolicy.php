<?php

namespace Astro\API\Policies;

use Astro\API\Policies\BasePolicy;
use App\Models\User;

class PermissionPolicy extends BasePolicy
{
    /**
     * Determine whether the user can list all permissions
     *
     * @param  User  $user
     * @return boolean
     */
    public function list(User $user)
    {
        return true;
    }

}
