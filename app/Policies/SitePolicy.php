<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Site;
use Illuminate\Auth\Access\HandlesAuthorization;

class SitePolicy
{
    use HandlesAuthorization;

    public function before($user, $ability)
    {
        if($user->isAdmin()){
            return true;
        }
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

    /**
     * Determine whether the user can view the Site.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Site  $site
     * @return boolean
     */
    public function read(User $user, Site $site)
    {
        return true;
    }

    /**
     * Determine whether the user can create Sites.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Site  $site
     * @return boolean
     */
    public function create(User $user, Site $site)
    {
        $pgs = $user->publishing_groups->keyBy($site->publishing_group->getKeyName());
        return $pgs->has($site->publishing_group->getKey());
    }

    /**
     * Determine whether the user can update the Site.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Site  $site
     * @return boolean
     */
    public function update(User $user, Site $site)
    {
        $pgs = $user->publishing_groups->keyBy($site->publishing_group->getKeyName());
        return $pgs->has($site->publishing_group->getKey());
    }

    /**
     * Determine whether the user can delete the Site.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Site  $site
     * @return boolean
     */
    public function delete(User $user, Site $site)
    {
        return false;
    }
}
