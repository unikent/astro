<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Route;
use Illuminate\Auth\Access\HandlesAuthorization;

class RoutePolicy
{
    use HandlesAuthorization;

    public function before($user, $ability)
    {
        return $user->isAdmin();
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
     * @param  \App\Models\Route  $route
     * @return boolean
     */
    public function read(User $user, Route $route)
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
     * @param  \App\Models\Route  $route
     * @return boolean
     */
    public function create(User $user, Route $route)
    {
        return true;
    }

    /**
     * Determine whether the user can update the definition.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Route  $route
     * @return boolean
     */
    public function update(User $user, Route $route)
    {
        return true;
    }

    /**
     * Determine whether the user can delete the definition.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Route  $route
     * @return boolean
     */
    public function delete(User $user, Route $route)
    {
        return true;
    }
}
