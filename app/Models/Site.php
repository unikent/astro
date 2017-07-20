<?php
namespace App\Models;

use App\Models\Route;
use App\Models\PublishingGroup;
use Illuminate\Database\Eloquent\Model;

class Site extends Model
{

	public $fillable = [
		'name',
		'publishing_group_id',
        'host',
        'path'
	];

	protected $definition = null;

	public function activeRoute()
	{
		return $this->hasOne(Route::class, 'site_id')->active();
	}

	public function routes()
	{
		return $this->hasMany(Route::class, 'site_id');
	}

	public function publishing_group()
	{
		return $this->belongsTo(PublishingGroup::class, 'publishing_group_id');
	}

}
