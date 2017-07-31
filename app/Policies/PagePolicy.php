<?php

namespace App\Policies;

use App\Models\PageContent;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class PagePolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can index page.
     *
     * @param  User  $user
     * @return boolean
     */
    public function index(User $user)
    {
        return true;
    }

    /**
     * Determine whether the user can view the page.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\PageContent  $page
     * @return boolean
     */
    public function read(User $user, PageContent $page)
    {
        // TODO: If page is published, OR if user has R/W access to site
        return true;
    }

    /**
     * Determine whether the user can create pages.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\PageContent  $page
     * @return boolean
     */
    public function create(User $user, PageContent $page)
    {
        // TODO: If user has W access to site
        return true;
    }

    /**
     * Determine whether the user can update the page.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\PageContent  $page
     * @return boolean
     */
    public function update(User $user, PageContent $page)
    {
        // TODO: If user has W access to site
        return true;
    }

    /**
     * Determine whether the user can publish the page.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\PageContent  $page
     * @return boolean
     */
    public function publish(User $user, PageContent $page)
    {
        return true;
    }

    /**
     * Determine whether the user can revert the page.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\PageContent  $page
     * @return boolean
     */
    public function revert(User $user, PageContent $page)
    {
        return true;
    }

    /**
     * Determine whether the user can delete the page.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\PageContent  $page
     * @return boolean
     */
    public function delete(User $user, PageContent $page)
    {
        // TODO: If user has W access to site
        return true;
    }

    /**
     * Determine whether the user can force-delete the page.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\PageContent  $page
     * @return boolean
     */
    public function forceDelete(User $user, PageContent $page)
    {
        return true;
    }
}
