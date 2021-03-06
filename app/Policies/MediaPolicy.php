<?php

namespace App\Policies;

use App\Policies\BasePolicy;
use App\Models\User;
use App\Models\Site;
use App\Models\Media;
use App\Models\Permission;

class MediaPolicy extends BasePolicy
{
    /**
     * Determine whether the user can index Media.
     *
     * @param User  $user
     * @param Site|null Site
     *
     * @return boolean
     */
    public function index(User $user, $site = null)
    {
        if(is_a($site, Site::class)) {
            return (new SitePolicy)->view($user, $site);
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
     * Accepts a Media item and a Site instance and uses these to determine whether the user can
     * upload media.
     *
     * @param  User  $user
     * @param  Media|array $aco
     * @param Site|null Site
     * @return boolean
     */
    public function create(User $user, $media, $site)
    {
        if(is_a($site, Site::class)) {
            return $user->hasPermissionForSite(Permission::ADD_IMAGE, $site->id);
        }

        return false;
    }

    /**
     * Determine whether the user can update the Media.
     *
     * Accepts a Media item or an array (a Media instance and a Site
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
     * Accepts a Media item or an array (a Media instance and a Site
     * instance) as the ACO.
     *
     * TODO: Remove this aco business.
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
            }
        }

        return false;
    }
}
