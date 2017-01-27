<?php namespace App\Models\Traits;

use Illuminate\Support\Facades\Config;

/**
 * Trait to automatically populate `created_by` and `updated_by` fields appropriately
 * when the model is saved.
 *
 * @see TrackedObserver
 *
 */
trait Tracked {

	/**
	 * Boot ths trait and add observers
	 */
	public static function bootTracked()
	{
		static::observe(new TrackedObserver);
	}

	/**
	 * Get the User that this model was created by
	 *
	 * @return mixed
	 */
	public function createdBy()
	{
		return $this->belongsTo(Config::get('auth.model'),'created_by');
	}

	/**
	 * Get the User that this model was last updated by
	 *
	 * @return mixed
	 */
	public function updatedBy()
	{
		return $this->belongsTo(Config::get('auth.model'),'updated_by');
	}

	/**
	 * Scope query to those models created by a User
	 *
	 * @param $query
	 * @param $userId
	 * @return mixed
	 */
	public function scopeCreatedBy($query,$userId)
	{
		return $query->where('created_by',$userId);
	}

	/**
	 * Scope query to those models updated by a User
	 *
	 * @param $query
	 * @param $userId
	 * @return mixed
	 */
	public function scopeUpdatedByUser($query,$userId)
	{
		return $query->where('updated_by',$userId);
	}

}
