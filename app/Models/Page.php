<?php

namespace App\Models;

use App\Models\Traits\Tracked;
use Illuminate\Database\Eloquent\Model;

class Page extends Model
{
	use Tracked;

	protected $fillable = [
		'title',
		'options',
		'layout_name',
		'layout_version',
	];

	protected $casts = [
        'options' => 'json',
        'is_site' => 'boolean',
        'is_published' => 'boolean',
	];


	public function canonical()
	{
		return $this->hasOne(Route::class, 'page_id')->where('is_canonical', '=', true);
	}

	public function routes()
	{
		return $this->hasMany(Route::class, 'page_id');
	}

	public function blocks()
	{
		return $this->hasMany(Block::class, 'page_id');
	}


	/**
	 * Scopes query to return Pages where 'is_site' is true
	 *
	 * @param  $query
	 * @return Collection
	 */
	public function scopeSites($query)
	{
		return $query->where('is_site', 1);
	}


	/**
	 * Deletes all blocks in the given Region
	 *
	 * @param  string $region
	 * @return void
	 */
	public function clearRegion($region){
		Block::deleteForPageRegion($this, $region);
	}


}
