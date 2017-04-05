<?php

namespace App\Policies\Definitions;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use App\Models\Definitions\Block as BlockDefinition;

class BlockPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can index Treatment.
     *
     * @param  User  $user
     * @return boolean
     */
    public function index(User $user)
    {
        return true;
    }

    /**
     * Determine whether the user can view the Block.
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
     * Determine whether the user can create Blocks.
     *
     * @param  \App\Models\User  $user
     * @return boolean
     */
    public function create(User $user)
    {
        return false;
    }

    /**
     * Determine whether the user can update the Block.
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
     * Determine whether the user can delete the Block.
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
