<?php
namespace Astro\API\Models\Traits;

use Illuminate\Support\Facades\Auth;

/**
 * Class TrackedObserver
 *
 * Implements event handlers for Tracked models
 *
 * @package Astro\API\Models\Traits
 * @see Astro\API\Models\Traits\Tracked
 */
class TrackedObserver
{
	public function creating($model)
	{
		// If there is an authorized user
		if(Auth::check())
		{
			$user = Auth::user();
			$primaryKeyName = $user->getKeyName();
			$model->created_by = $user->$primaryKeyName;
		}
		else
		{
			$model->created_by = 0;
		}
	}
	public function saving($model)
	{
		// If there is an authorized user
		if(Auth::check())
		{
			$user = Auth::user();
			$primaryKeyName = $user->getKeyName();
			$model->updated_by = $user->$primaryKeyName;
		}
		else
		{
			$model->updated_by = 0;
		}
	}
}
