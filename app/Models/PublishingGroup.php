<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * A PublishingGroup determines which users can edit which sites.
 * @package App\Models
 */
class PublishingGroup extends Model
{

	public $fillable = [
		'name',
	];

	protected $definition = null;


	public function users()
	{
		return $this->belongsToMany(User::class, 'publishing_groups_users');
	}

	public function sites()
	{
		return $this->hasMany('sites');
	}

}
