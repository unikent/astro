<?php
namespace App\Models\Traits;

use App\Models\Page;
use App\Models\PublishedPage;

/**
 *
 */
trait Routable  {

	public function page()
	{
		return $this->belongsTo(Page::class, 'page_id');
	}

	public function published_page()
	{
		return $this->hasOne(PublishedPage::class, 'page_id', 'page_id')->latest()->limit(1);
	}


	/**
	 * Attempts to retrieve a model by path
	 *
	 * @param  string $path
	 * @return \Illuminate\Database\Eloquent\Model
	 */
	public static function findByPath($path)
	{
		return static::where('path', '=', $path)->first();
	}

	/**
	 * Attempts to retrieve a model by path, throws Exception when not found
	 *
	 * @param  string $path
	 * @return \Illuminate\Database\Eloquent\Model
	 * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
	 */
	public static function findByPathOrFail($path)
	{
		return static::where('path', '=', $path)->firstOrFail();
	}

}
