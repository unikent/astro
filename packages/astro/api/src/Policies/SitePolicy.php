<?php

namespace Astro\API\Policies;

use Astro\API\Models\Permission;
use App\Models\User;
use Astro\API\Models\Site;

class SitePolicy extends BasePolicy
{
    /**
     * All users can view the list of Sites, the list is restricted elsewhere.
     * @param  User  $user
     * @return boolean
     */
    public function index(User $user)
    {
        return true;
    }

    /**
     * Determine whether the user can view the Site.
     *
     * @param  \App\Models\User  $user
     * @param  \Astro\API\Models\Site  $site
     * @return boolean
     */
    public function view(User $user, Site $site)
    {
		return $user->hasPermissionForSite(Permission::VIEW_SITE, $site->id);
    }

	/**
	 * Can the user move pages on this site?
	 * @param User $user
	 * @param Site $site
	 * @return bool
	 */
	public function move(User $user, Site $site)
	{
		return $user->hasPermissionForSite(Permission::MOVE_PAGE, $site->id);
	}

    /**
     * Determine whether the user can create Sites.
     *
     * @param  \App\Models\User  $user
     * @param  \Astro\API\Models\Site  $site
     * @return boolean
     */
    public function create(User $user)
    {
    	return $user->isAdmin();
    }

	/**
	 * Can the given user assign users to this site?
	 * @param User $user
	 * @param Site $site
	 * @return boolean
	 */
    public function assign(User $user, Site $site)
	{
		return $user->hasPermissionForSite(Permission::ASSIGN_SITE_PERMISSIONS, $site->id);
	}

    /**
     * Determine whether the user can update the Site.
     *
     * @param  \App\Models\User  $user
     * @param  \Astro\API\Models\Site  $site
     * @return boolean
     */
    public function update(User $user, Site $site)
    {
    	return $user->hasPermissionForSite(Permission::EDIT_SITE, $site->id);
    }

	/**
	 * Can the user update the site options (things like menu, etc)
	 * @param User $user
	 * @param Site $site
	 * @return bool
	 */
    public function updateOptions(User $user, Site $site)
	{
		return $user->hasPermissionForSite([Permission::EDIT_SITE_OPTIONS, Permission::EDIT_SITE], $site->id);
	}

    /**
     * Determine whether the user can delete the Site.
     *
     * @param  \App\Models\User  $user
     * @param  \Astro\API\Models\Site  $site
     * @return boolean
     */
    public function delete(User $user, Site $site)
    {
        return $user->hasPermissionForSite(Permission::DELETE_SITE, $site->id);
    }

	/**
	 * Can the user move pages on this site?
	 * @param User $user
	 * @param Site $site
	 * @return bool
	 */
    public function movepages(User $user, Site $site)
	{
		return $user->hasPermissionForSite(Permission::MOVE_PAGE, $site->id);
	}
}
