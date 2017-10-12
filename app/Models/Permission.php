<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;


class Permission extends Model
{
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

	public function roles()
	{
		return $this->belongsToMany(Role::class, 'role_permissions');
	}
}