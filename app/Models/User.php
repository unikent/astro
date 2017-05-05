<?php

namespace App\Models;

use KentAuth\Models\User as KentUser;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends KentUser
{

	protected $hidden = [ 'api_token', 'created_at', 'updated_at', 'created_by', 'updated_by' ];


    /**
     * Create a new Eloquent model instance.
     *
     * @param  array  $attributes
     * @return void
     */
    public function __construct(array $attributes = [])
    {
   		parent::__construct($attributes);

   		$this->api_token = $this->api_token ?: str_random(255);
	}

    public function publishing_groups()
    {
        return $this->belongsToMany(PublishingGroup::class, 'publishing_groups_users');
    }

}
