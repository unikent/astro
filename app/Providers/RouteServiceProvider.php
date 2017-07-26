<?php

namespace App\Providers;

use App\Models\PageContent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Models\Definitions\Block as BlockDefinition;
use App\Models\Definitions\Layout as LayoutDefinition;
use App\Models\Definitions\Region as RegionDefinition;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;

class RouteServiceProvider extends ServiceProvider
{
	/**
	 * This namespace is applied to your controller routes.
	 *
	 * In addition, it is set as the URL generator's root namespace.
	 *
	 * @var string
	 */
	protected $namespace = 'App\Http\Controllers';

	/**
	 * Define your route model bindings, pattern filters, etc.
	 *
	 * @return void
	 */
	public function boot()
	{
		parent::boot();

	    Route::bind('page', function($value){
	        return PageContent::withTrashed()->where('id', '=', $value)->firstOrFail();
	    });

	    Route::bind('block_definition', function($value){
	        $path = BlockDefinition::locateDefinitionOrFail($value, request()->get('version', null));
	        return BlockDefinition::fromDefinitionFile($path);
	    });

	    Route::bind('layout_definition', function($value){
	        $path = LayoutDefinition::locateDefinitionOrFail($value, request()->get('version', null));
	        return LayoutDefinition::fromDefinitionFile($path);
	    });

	    Route::bind('region_definition', function($value){
	        $path = RegionDefinition::locateDefinitionOrFail($value, request()->get('version', null));
	        return RegionDefinition::fromDefinitionFile($path);
	    });
	}

	/**
	 * Define the routes for the application.
	 *
	 * @return void
	 */
	public function map()
	{
		$this->mapApiRoutes();

		$this->mapWebRoutes();

		//
	}

	/**
	 * Define the "web" routes for the application.
	 *
	 * These routes all receive session state, CSRF protection, etc.
	 *
	 * @return void
	 */
	protected function mapWebRoutes()
	{
		Route::middleware('web')
			 ->namespace($this->namespace)
			 ->group(base_path('routes/web.php'));
	}

	/**
	 * Define the "api" routes for the application.
	 *
	 * These routes are typically stateless.
	 *
	 * @return void
	 */
	protected function mapApiRoutes()
	{
		Route::prefix('api')
			 ->namespace('App\Http\Controllers\Api')
			 ->middleware('api')
			 ->group(base_path('routes/api.php'));
	}
}
