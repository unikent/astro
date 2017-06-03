<?php
namespace App\Models\Contracts;

interface Routable {

	public function page();

	public function published_page();

	/**
	 * Attempts to retrieve a model by path
	 *
	 * @param  string $path
	 * @return \Illuminate\Database\Eloquent\Model
	 */
	public static function findByPath($path);

	/**
	 * Attempts to retrieve a model by path, throws Exception when not found
	 *
	 * @param  string $path
	 * @return \Illuminate\Database\Eloquent\Model
	 * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
	 */
	public static function findByPathOrFail($path);

}
