<?php 

namespace App\Models;

use Illuminate\Database\Eloquent\Model;


class Permission extends Model
{
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