<?php 

namespace App\Models;

use Illuminate\Database\Eloquent\Model;


class Role extends Model
{
	const OWNER = 'Site Owner';
	const EDITOR = 'Editor';
	const CONTRIBUTOR = 'Contributor';
	const ADMIN = 'Admin';

	/**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['name'];
    
	public function permissions()
	{
		return $this->belongsToMany(Permission::class, 'role_permissions');
	}

	public function users()
	{
		return $this->belongsToMany(User::class, 'user_site_roles');
	}
}