<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;


class Permission extends Model
{
  /**
   * Define the permission strings with constants to catch sneaky typos.
   */
	const CREATE_SUBSITE = 'Create Subsite';
	const EDIT_SUBSITE = 'Edit Subsite';
	const DELETE_SUBSITE = 'Delete Subsite';
	const EDIT_MENU = 'Edit Menu';
	const MOVE_SUBSITE = 'Move Subsite';
	const ADD_PAGE = 'Add Page';
	const EDIT_PAGE = 'Edit Page';
	const DELETE_PAGE = 'Delete Page';
	const MOVE_PAGE = 'Move Page';
	const ADD_IMAGE = 'Add Image';
	const EDIT_IMAGE = 'Edit Image';
	const USE_IMAGE = 'Use Image';
	const PUBLISH_PAGE = 'Publish Page';
	const PREVIEW_PAGE = 'Preview page';
	const APPROVAL = 'Approval';
	const ASSIGN_SITE_PERMISSIONS = 'Assign Sites permissions';
	const ASSIGN_PAGE_PERMISSIONS = 'Assign page permisions';
	const ASSIGN_SUBSITE_PERMISSIONS = 'Assign Subsite permissions';
	const CREATE_SITE = 'Create Site';
	const DELETE_SITE = 'Delete Site';
	const EDIT_SITE = 'Edit Site';
	const MOVE_SITE = 'Move Site';
	const TEMPLATE_MANIPULATION = 'Template Manipulation';

  	/**
	 * Create an array mapping permission-name => [role1,role2,...] for every permission.
	 * @return array - Array keyed by permission name with values containing the names of the roles that have that permission.
	 */
	public static function toArrayWithRoles()
	{
		$data = [];
		foreach(Permission::with('roles')->orderBy('name')->get() as $p){
			$data[$p->name] = $p->roles->pluck('name');
		}
		return $data;
	}

	/**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['name'];
    
	public function roles()
	{
		return $this->belongsToMany(Role::class, 'role_permissions');
	}
}