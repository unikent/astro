<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Page;
use Illuminate\Auth\Access\HandlesAuthorization;

class RoutePolicy
{
    use HandlesAuthorization;

    public function before($user, $ability)
    {
        if($user->isAdmin())
        {
            return true;
        }
    }

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
     * @param  \App\Models\Page  $route
     * @return boolean
     */
    public function read(User $user, Page $route)
    {
        if($route->isActive()){
            return true;
        } else {
            // TODO: Check if user is sufficiently privileged to view inactive route
        }

        return false;
    }

    /**
     * Determine whether the user can create definitions.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Page  $route
     * @return boolean
     */
    public function create(User $user, Page $route)
    {
        return true;
    }

    /**
     * Determine whether the user can update the definition.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Page  $route
     * @return boolean
     */
    public function update(User $user, Page $route)
    {
        return true;
    }

    /**
     * Determine whether the user can delete the definition.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Page  $route
     * @return boolean
     */
    public function delete(User $user, Page $route)
    {
        return true;
    }
}
