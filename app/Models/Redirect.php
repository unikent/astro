<?php
namespace App\Models;

use App\Models\Traits\Tracked;
use DB;
use Exception;
use Illuminate\Database\Eloquent\Model;

/**
 * A Redirect records the paths of pages which have been moved elsewhere.
 * @package App\Models
 */
class Redirect extends Model
{
	use Tracked;
}
