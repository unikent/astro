<?php
namespace App\Models\Traits;

/**
 *
 */
trait Pathable {

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
