<?php

namespace App\Policies\Definitions;

use App\Policies\BasePolicy;
use App\Models\User;
use App\Models\Definitions\SiteDefinition;

class SiteDefinitionPolicy extends BasePolicy
{
    /**
     * Determine whether the user can index definitions.
     *
     * @param  User  $user
     * @return boolean
     */
    public function index(User $user)
    {
        return true;
    }

    /**
     * Determine whether the user can view the definition.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Definitions\Layout  $definition
     * @return boolean
     */
    public function read(User $user, SiteDefinition $definition)
    {
        return true;
    }

}
