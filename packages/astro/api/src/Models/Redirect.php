<?php
namespace Astro\API\Models;

use Astro\API\Models\Traits\Tracked;
use DB;
use Exception;
use Illuminate\Database\Eloquent\Model;

/**
 * A Redirect records the paths of pages which have been moved elsewhere.
 * @package Astro\API\Models
 */
class Redirect extends Model
{
	use Tracked;
}
