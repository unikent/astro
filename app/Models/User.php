<?php

namespace App\Models;

use KentAuth\Models\User as KentUser;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends KentUser
{

    protected $casts = [
        'settings' => 'json'
    ];

	protected $hidden = [ 'api_token', 'created_at', 'updated_at', 'created_by', 'updated_by' ];

    protected $attributes = [
        'settings' => '{}'
    ];

    /**
     * Create a new Eloquent model instance.
     *
     * @param  array  $attributes
     * @return void
     */
    public function __construct(array $attributes = [])
    {
   		parent::__construct($attributes);

   		$this->api_token = $this->api_token ?: str_random(191); // Max string length without MySQL 5.7, see commit 7c90098
	}

    public function publishing_groups()
    {
        return $this->belongsToMany(PublishingGroup::class, 'publishing_groups_users');
    }

    /**
     * Can this user edit the site with this id?
     * @param Site $site The ID of the site to check for.
     * @return mixed
     */
    public function canEditSite(Site $site)
    {
      return $this->isAdmin() || $site->publishing_group->users()->contains($this->id);
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


}
