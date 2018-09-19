<?php

namespace App\Models;

use KentAuth\Models\User as KentUser;

class User extends KentUser
{

	protected $casts = [
		'settings' => 'json'
	];

	protected $hidden = [ 'api_token', 'created_at', 'updated_at'];

	protected $attributes = [
		'settings' => '{}'
	];

	public static $global_roles = ['admin', 'user', 'viewer'];

	/**
	 * Create a new Eloquent model instance.
	 *
	 * @param  array  $attributes
	 * @return void
	 */
	public function __construct(array $attributes = [])
	{
		parent::__construct($attributes);

		$this->generateAPIToken();
	}

	/**
	 * Generate a new API token.
	 * @param bool $override Whether or not to override an existing token.
	 * @return void
	 */
	public function generateAPIToken($override = false)
	{
		$this->api_token = $this->api_token && !$override ? $this->api_token : str_random(191); // Max string length without MySQL 5.7, see commit 7c90098
	}

	/**
	 * Can this user edit the site with this id?
	 * @param Site $site The ID of the site to check for.
	 * @return mixed
	 */
	public function canEditSite(Site $site)
	{
	  return $this->isAdmin() || $this->hasPermissionForSite(Permission::EDIT_SITE, $site->id);
	}

	/**
	 * Returns true if users' role is set to 'viewer'
	 *
	 * @return boolean
	 */
	public function isViewer()
	{
		return ($this->role == 'viewer');
	}

	/**
	 * Returns true if users' role is set to 'admin'
	 *
	 * @return boolean
	 */
	public function isAdmin()
	{
		return ($this->role == 'admin');
	}

	public function setRememberToken($value)
	{
		return false;
	}

	/**
	 * The pivot table class which contains information about which roles on which sites the user has.
	 * To eager load the roles and sites for a user, load('roles.role,roles.site')
	 * @return \Illuminate\Database\Eloquent\Relations\HasMany
	 */
	public function roles()
	{
		return $this->hasMany(UserSiteRole::class);
	}

	/**
	 * Does this user have the specified permission for the specified Site?
	 * @param $permission
	 * @param Site $site
	 * @return bool
	 */
	public function hasPermissionForSite($permission, $site_id)
	{
		$permission = is_array($permission) ? $permission : [$permission];
		$role = $this->roles()
				->where('site_id', '=', $site_id)
				->first();
		if($role){
			// user->roles is UserSiteRole relationship, need to get the role from that
			return $role->role->permissions()->whereIn('slug', $permission)->count() > 0;
		}
		return false;
	}
}
