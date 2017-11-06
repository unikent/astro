<?php

namespace App\Policies;

use App\Models\Page;
use App\Models\Permission;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class PagePolicy
{
    use HandlesAuthorization;

	public function before(User $user, $ability)
	{
		if($user->isAdmin()){
			return true;
		}
	}

	/**
     * Determine whether the user can view the page.
     *
     * @param  \App\Models\User  $user
     * @param  Page  $page
     * @return boolean
     */
    public function read(User $user, Page $page)
    {
    	return $user->hasPermissionForSite([
    		Permission::PREVIEW_PAGE,
			Permission::PUBLISH_PAGE,
			Permission::EDIT_SITE
			], $page->site_id);
    }

    /**
     * Determine whether the user can create pages.
     *
     * @param  \App\Models\User  $user
     * @param  Page  $page
     * @return boolean
     */
    public function create(User $user, $site_id)
    {
    	return $user->hasPermissionForSite(Permission::ADD_PAGE, $site_id);
    }

    /**
     * Determine whether the user can update the page.
     *
     * @param  \App\Models\User  $user
     * @param  Page  $page
     * @return boolean
     */
    public function update(User $user, Page $page)
    {
        return $user->hasPermissionForSite(Permission::EDIT_PAGE, $page->site_id);
    }

    /**
     * Determine whether the user can publish the page.
     *
     * @param  \App\Models\User  $user
     * @param  Page  $page
     * @return boolean
     */
    public function publish(User $user, Page $page)
    {
        return $user->hasPermissionForSite(Permission::PUBLISH_PAGE, $page->site_id);
    }

	/**
	 * Determine whether the user can publish the page.
	 *
	 * @param  \App\Models\User  $user
	 * @param  Page  $page
	 * @return boolean
	 */
	public function unpublish(User $user, Page $page)
	{
		return $user->hasPermissionForSite(Permission::UNPUBLISH_PAGE, $page->site_id);
	}

    /**
     * Determine whether the user can revert the page.
     *
     * @param  \App\Models\User  $user
     * @param  Page  $page
     * @return boolean
     */
    public function revert(User $user, Page $page)
    {
        return $user->hasPermissionForSite(Permission::REVERT_PAGE, $page->site_id);
    }

    /**
     * Determine whether the user can delete the page.
     *
     * @param  \App\Models\User  $user
     * @param  Page  $page
     * @return boolean
     */
    public function delete(User $user, Page $page)
    {
        return $user->hasPermissionForSite(Permission::DELETE_PAGE, $page->site_id);
    }

    /**
     * Determine whether the user can force-delete the page.
     *
     * @param  \App\Models\User  $user
     * @param  Page  $page
     * @return boolean
     */
    public function forceDelete(User $user, Page $page)
    {
        return $user->hasPermissionForSite(Permission::DELETE_PAGE, $page->site_id);
    }
}
