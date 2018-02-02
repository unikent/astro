<?php 

namespace App\Models;

use Illuminate\Database\Eloquent\Model;


class Role extends Model
{
	const OWNER = 'site.owner';
	const EDITOR = 'site.editor';
	const CONTRIBUTOR = 'site.contributor';
	const ADMIN = 'admin';

	/**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['name', 'slug'];
    
	public function permissions()
	{
		return $this->belongsToMany(Permission::class, 'role_permissions');
	}

	public function users()
	{
		return $this->belongsToMany(User::class, 'user_site_roles');
	}
}