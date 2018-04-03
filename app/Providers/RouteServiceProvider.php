<?php

namespace App\Providers;

use App\Models\Page;
use App\Models\Site;
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

		/** @var \Illuminate\Routing\UrlGenerator $url */
		$url = $this->app['url'];
		// Force the application URL
		$url->forceRootUrl(config('app.url'));

		// only bind draft pages by id
		Route::bind('page', function ($value) {
			return Page::draft()->where('id', '=', $value)->firstOrFail();
		});

		Route::bind('site', function($value){
			return Site::where('id', '=', $value)->firstOrFail();
		});

		Route::bind('block_definition', function ($value) {
			$path = BlockDefinition::locateDefinitionOrFail($value);
			return BlockDefinition::fromDefinitionFile($path);
		});

		Route::bind('layout_definition', function ($value) {
			$path = LayoutDefinition::locateDefinitionOrFail($value);
			return LayoutDefinition::fromDefinitionFile($path);
		});

		Route::bind('region_definition', function ($value) {
			$path = RegionDefinition::locateDefinitionOrFail($value);
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
		if(!env('DISABLE_API_ROUTES') || strtolower(env('DISABLE_API_ROUTES')) == 'false' ) {
			$this->mapApiRoutes();
		}
		if(!env('DISABLE_PREVIEW_ROUTES') || strtolower(env('DISABLE_PREVIEW_ROUTES')) == 'false') {
			$this->mapPreviewRoutes();
		}
		if(!env('DISABLE_WEB_ROUTES') || strtolower(env('DISABLE_WEB_ROUTES')) == 'false' ) {
			$this->mapWebRoutes();
		}
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
	 * Define the "preview" routes for the application.
	 *
	 * These routes are used to preview draft and published versions of pages and all receive session state, CSRF protection, etc.
	 *
	 * @return void
	 */
	protected function mapPreviewRoutes()
	{
		Route::middleware('web')
			->namespace($this->namespace)
			->group(base_path('routes/preview.php'));
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
