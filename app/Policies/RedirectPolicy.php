<?php

namespace App\Policies;

use App\Policies\BasePolicy;
use App\Models\User;
use Astro\API\Models\Redirect;

class RedirectPolicy extends BasePolicy
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
     * @param  \Astro\API\Models\Redirect  $redirect
     * @return boolean
     */
    public function read(User $user, Redirect $redirect)
    {
        return true; // TODO: Under what circumstances should a Redirect not return a Page?
    }

    /**
     * Determine whether the user can create definitions.
     *
     * @param  \App\Models\User  $user
     * @param  \Astro\API\Models\Redirect  $redirect
     * @return boolean
     */
    public function create(User $user, Redirect $redirect)
    {
        return true;
    }

    /**
     * Determine whether the user can update the definition.
     *
     * @param  \App\Models\User  $user
     * @param  \Astro\API\Models\Redirect  $redirect
     * @return boolean
     */
    public function update(User $user, Redirect $redirect)
    {
        return true;
    }

    /**
     * Determine whether the user can delete the definition.
     *
     * @param  \App\Models\User  $user
     * @param  \Astro\API\Models\Redirect  $redirect
     * @return boolean
     */
    public function delete(User $user, Redirect $redirect)
    {
        return true;
    }
}
