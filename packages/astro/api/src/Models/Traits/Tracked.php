<?php namespace Astro\API\Models\Traits;

use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
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

    /**
     * Intercepts bulk inserts which would normally be sent straight to the query builder
     * and adds timestamps and ...by values
     * @param array $inserts
     */
	public static function __callStatic($func, $args)
    {
        if($func == 'insert' && is_array($args[0])) {
            $inserts =& $args[0];
            $n_inserts = count($inserts);
            $by = null;
            if (Auth::check()) {
                $user = Auth::user();
                $primaryKeyName = $user->getKeyName();
                $by = $user->$primaryKeyName;
            }
            $created = Carbon::now();
            for ($i = 0; $i < $n_inserts; ++$i) {
                if (is_array($inserts[$i])) {
                    if (!isset($inserts[$i]['created_at'])) {
                        $inserts[$i]['created_at'] = $created;
                    }
                    if (!isset($inserts[$i]['updated_at'])) {
                        $inserts[$i]['updated_at'] = $created;
                    }
                    if (!isset($inserts[$i]['created_by'])) {
                        $inserts[$i]['created_by'] = $by;
                    }
                    if (!isset($inserts[$i]['updated_by'])) {
                        $inserts[$i]['updated_by'] = $by;
                    }
                }
            }
        }
        return parent::__callStatic($func,$args);
    }

}
