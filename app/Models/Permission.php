<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;


class Permission extends Model
{
	/**
	 * Define the permission strings with constants to catch sneaky typos.
	 */
	const REVERT_PAGE = 'page.revert';
	const CREATE_SUBSITE = 'subsite.create';
	const EDIT_SUBSITE = 'subsite.edit';
	const DELETE_SUBSITE = 'subsite.delete';
	const EDIT_MENU = 'menu.edit';
	const MOVE_SUBSITE = 'subsite.move';
	const ADD_PAGE = 'page.add';
	const EDIT_PAGE = 'page.edit';
	const DELETE_PAGE = 'page.delete';
	const MOVE_PAGE = 'page.move';
	const ADD_IMAGE = 'image.add';
	const EDIT_IMAGE = 'image.edit';
	const USE_IMAGE = 'image.use';
	const PUBLISH_PAGE = 'page.publish';
	const PREVIEW_PAGE = 'page.preview';
	const APPROVAL = 'page.approve';
	const ASSIGN_SITE_PERMISSIONS = 'permissions.site.assign';
	const ASSIGN_PAGE_PERMISSIONS = 'permissions.page.assign';
	const ASSIGN_SUBSITE_PERMISSIONS = 'permissions.subsite.assign ';
	const CREATE_SITE = 'site.create';
	const LIST_SITES = 'site.list';
	const DELETE_SITE = 'site.delete';
	const EDIT_SITE = 'site.edit';
	const MOVE_SITE = 'site.move';
	const TEMPLATE_MANIPULATION = 'templates.manipulate';

	/**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['name','slug'];
    
	public function roles()
	{
		return $this->belongsToMany(Role::class, 'role_permissions');
	}
}