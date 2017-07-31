<?php
namespace App\Models;

use DB;
use Exception;
use App\Models\Page;
use App\Models\Traits\Routable;
use Illuminate\Database\Eloquent\Model;
use App\Models\Contracts\Routable as RoutableContract;

class Redirect extends Model implements RoutableContract
{
	use Routable;

	protected $fillable = [
		'path',
		'page_content_id',
	];

    /**
     * Creates a new Redirect from a Route object. Removes any Redirects
     * that already exist using the same path.
     *
     * @param Page $route
     * @return Redirect $redirect
     *
     * @throws \Exception
     */
    public static function createFromRoute(Page $route)
    {
    	DB::beginTransaction();

    	try {
    		// Check for (and remove) duplicates
    		static::where('path', $route->path)->delete();

    		$published_page = $route->published_page;
//    		var_dump($published_page);
    		// Create a new Redirect
    		$redirect = new Redirect([ 'path' => $route->path, 'page_content_id' => $published_page->page_content_id ]);
    		$redirect->save();

	    	DB::commit();
    	} catch(Exception $e){
    		DB::rollback();
    		throw $e;
    	}

    	return $redirect;
    }
}
