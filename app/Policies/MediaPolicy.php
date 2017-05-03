<?php

namespace App\Policies;

use Gate;
use App\Models\User;
use App\Models\Site;
use App\Models\Media;
use App\Models\PracticeGroup;
use Illuminate\Auth\Access\HandlesAuthorization;

class MediaPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can index Media.
     *
     * @param  User  $user
     * @return boolean
     */
    public function index(User $user)
    {
        return true;
    }

    /**
     * Determine whether the user can view the Media.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Media  $media
     * @return boolean
     */
    public function read(User $user, Media $media)
    {
        return true;
    }

    /**
     * Determine whether the user can create Media.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Media  $media
     * @return boolean
     */
    public function create(User $user, Media $media)
    {
        return true;
    }

    /**
     * Determine whether the user can update the Media.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Media  $media
     * @return boolean
     */
    public function update(User $user, Media $media)
    {
        return true;
    }

    /**
     * Determine whether the user can delete the Media.
     *
     * @param  \App\Models\User  $user
     * @param  array|Media  $acl
     * @return boolean
     */
    public function delete(User $user, $acl)
    {
        if(is_array($acl, Media::class)){
            return Gate::can('update', $acl[1]);

        } elseif(is_a($acl, Media::class)){
            return false;

        } else {
            return false; // Throw exception

        }
    }
}
