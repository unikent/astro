<?php 

namespace App\Models;

use Illuminate\Database\Eloquent\Model;


class Role extends Model
{
	public function permissions()
	{
		return $this->belongsToMany(Permission::class, 'role_permissions');
	}

	public function users()
	{
		return $this->belongsToMany(User::class, 'user_site_roles');
	}
}