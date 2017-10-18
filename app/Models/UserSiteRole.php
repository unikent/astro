<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * The pivot table used for mapping a user to a role on a site.
 * We need this class to simplify eloquent queries that get users with roles and sites together.
 * @package App\Models
 */
class UserSiteRole extends Model
{
	protected $table = 'user_site_roles';
    protected $fillable = [
        'user_id',
        'site_id',
        'role_id'
    ];
	public $timestamps = false;
	/**
	 * The role in this relationship.
	 * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
	 */
	public function role()
	{
		return $this->belongsTo(Role::class, 'role_id');
	}

	/**
	 * The user in this relationship.
	 * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
	 */
	public function user()
	{
		return $this->belongsTo(User::class, 'user_id');
	}

	/**
	 * The site in this relationship.
	 * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
	 */
	public function site()
	{
		return $this->belongsTo(Site::class, 'site_id');
	}
}