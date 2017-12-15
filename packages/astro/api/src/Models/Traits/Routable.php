<?php
namespace Astro\API\Models\Traits;

use Astro\API\Models\Page;
use Astro\API\Models\Revision;

/**
 * @deprecated
 */
trait Routable  {

	public function page()
	{
		return $this->belongsTo(Page::class, 'page_id')->withTrashed();
	}

	public function published_page()
	{
		return $this->hasOne(Revision::class, 'page_id', 'page_id')->latest()->limit(1);
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
