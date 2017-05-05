<?php

namespace App\Policies;

use Gate;
use App\Models\Site;
use App\Models\User;
use App\Models\Media;
use App\Models\PublishingGroup;
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
     * @param  array $aco
     * @return boolean
     */
    public function create(User $user, Array $aco)
    {
        if(isset($aco[1])){
            if(is_a($aco[1], Site::class)){
                return (new SitePolicy)->update($user, $aco[1]);

            } elseif(is_a($aco[1], PublishingGroup::class)){
                $pgs = $user->publishing_groups->keyBy($aco[1]->getKeyName());
                return $pgs->has($aco[1]->getKey());

            }
        }

        return false;
    }

    /**
     * Determine whether the user can update the Media.
     *
     * @param  \App\Models\User  $user
     * @param  array  $aco
     * @return boolean
     */
    public function update(User $user, Array $aco)
    {
        return false;
    }

    /**
     * Determine whether the user can delete the Media.
     *
     * @param  \App\Models\User  $user
     * @param  array  $aco
     * @return boolean
     */
    public function delete(User $user, Array $aco)
    {
        if(isset($aco[1])){
            if(is_a($aco[1], Site::class)){
                return (new SitePolicy)->update($user, $aco[1]);

            } elseif(is_a($aco[1], PublishingGroup::class)){
                $pgs = $user->publishing_groups->keyBy($aco[1]->getKeyName());
                return $pgs->has($aco[1]->getKey());

            }
        }

        return false;
    }
}
