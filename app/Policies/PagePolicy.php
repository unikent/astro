<?php

namespace App\Policies;

use App\Models\Page;
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
     * @param  \App\Models\Page  $page
     * @return boolean
     */
    public function read(User $user, Page $page)
    {
        // TODO: If page is published, OR if user has R/W access to site
        return true;
    }

    /**
     * Determine whether the user can create pages.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Page  $page
     * @return boolean
     */
    public function create(User $user, Page $page)
    {
        // TODO: If user has W access to site
        return true;
    }

    /**
     * Determine whether the user can update the page.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Page  $page
     * @return boolean
     */
    public function update(User $user, Page $page)
    {
        // TODO: If user has W access to site
        return true;
    }

    /**
     * Determine whether the user can update the page.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Page  $page
     * @return boolean
     */
    public function publish(User $user, Page $page)
    {
        return true;
    }

    /**
     * Determine whether the user can delete the page.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Page  $page
     * @return boolean
     */
    public function delete(User $user, Page $page)
    {
        // TODO: If user has W access to site
        return true;
    }
}
