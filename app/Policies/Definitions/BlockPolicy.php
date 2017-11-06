<?php

namespace App\Policies\Definitions;

use App\Policies\BasePolicy;
use App\Models\User;
use App\Models\Definitions\Block as BlockDefinition;

class BlockPolicy extends BasePolicy
{
    /**
     * Determine whether the user can index definition.
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
     * @param  \App\Models\Definitions\Block  $definition
     * @return boolean
     */
    public function read(User $user, BlockDefinition $definition)
    {
        return true;
    }

    /**
     * Determine whether the user can create definitions.
     *
     * @param  \App\Models\User  $user
     * @return boolean
     */
    public function create(User $user)
    {
        return false;
    }

    /**
     * Determine whether the user can update the definition.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Definitions\Block  $definition
     * @return boolean
     */
    public function update(User $user, BlockDefinition $definition)
    {
        return false;
    }

    /**
     * Determine whether the user can delete the definition.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Definitions\Block  $definition
     * @return boolean
     */
    public function delete(User $user, BlockDefinition $definition)
    {
        return false;
    }
}
