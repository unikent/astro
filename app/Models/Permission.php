<?php 

namespace App\Models;

use Illuminate\Database\Eloquent\Model;


class Permission extends Model
{
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