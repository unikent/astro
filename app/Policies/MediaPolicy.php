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


    public function before($user, $ability)
    {
        return $user->isAdmin();
    }


    /**
     * Determine whether the user can index Media.
     *
     * Accepts a string or an array (containing a Media class string and a Site/PublishingGroup
     * instance) as the ACO.
     *
     * @param User  $user
     * @param string|array $aco
     * @return boolean
     */
    public function index(User $user, $aco = null)
    {
        if(is_array($aco) && isset($aco[1])){
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
     * Determine whether the user can view the Media.
     *
     * @param  User  $user
     * @param  Media  $media
     * @return boolean
     */
    public function read(User $user, Media $media)
    {
        return true;
    }

    /**
     * Determine whether the user can create Media.
     *
     * Accepts a Media item or an array (a Media instance and a PublishingGroup/Site
     * instance) as the ACO.
     *
     * @param  User  $user
     * @param  Media|array $aco
     * @return boolean
     */
    public function create(User $user, $aco)
    {
        if(is_array($aco) && isset($aco[1])){
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
     * Accepts a Media item or an array (a Media instance and a PublishingGroup/Site
     * instance) as the ACO.
     *
     * @param  User  $user
     * @param  Media|array  $aco
     * @return boolean
     */
    public function update(User $user, $aco)
    {
        return false;
    }

    /**
     * Determine whether the user can delete the Media.
     *
     * Accepts a Media item or an array (a Media instance and a PublishingGroup/Site
     * instance) as the ACO.
     *
     * @param  User  $user
     * @param  Media|array  $aco
     * @return boolean
     */
    public function delete(User $user, Array $aco)
    {
        if(is_array($aco) && isset($aco[1])){
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
