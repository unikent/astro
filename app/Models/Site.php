<?php
namespace App\Models;

use App\Models\Route;
use App\Models\PublishingGroup;
use Illuminate\Database\Eloquent\Model;

class Site extends Model
{

	public $fillable = [
		'name',
	];

	protected $definition = null;

	public function route()
	{
		return $this->belongsTo(Route::class, 'site_id');
	}

	public function publishingGroup()
	{
		return $this->hasMany(PublishingGroup::class, 'publishing_group_id');
	}

}
