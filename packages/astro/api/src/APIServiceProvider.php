<?php

namespace Astro\API;

use Astro\API\Console\Commands\AddSite;
use Astro\API\Console\Commands\AddUser;
use Astro\API\Console\Commands\CheckDefns;
use Astro\API\Console\Commands\ManageAdmins;
use Astro\API\Console\Commands\SetupPermissions;
use Astro\API\Models\Definitions\Block;
use Astro\API\Models\Definitions\Layout;
use Astro\API\Models\Definitions\Region;
use Astro\API\Models\Page;
use Illuminate\Database\Eloquent\Factory;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class APIServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
    	$this->bootRoutes();
		if ($this->app->runningInConsole()) {
			$this->commands([
				AddSite::class,
				AddUser::class,
				CheckDefns::class,
				ManageAdmins::class,
				SetupPermissions::class
			]);
			$this->loadMigrationsFrom(__DIR__.'/Database/migrations');
		}
    }

	/**
	 * Setup all routing functionality relevant to the API.
	 */
    public function bootRoutes()
	{
		// only bind draft pages by id
		Route::bind('page', function ($value) {
			return Page::draft()->where('id', '=', $value)->firstOrFail();
		});

		Route::bind('block_definition', function ($value) {
			$path = Block::locateDefinitionOrFail($value);
			return Block::fromDefinitionFile($path);
		});

		Route::bind('layout_definition', function ($value) {
			$path = Layout::locateDefinitionOrFail($value);
			return Layout::fromDefinitionFile($path);
		});

		Route::bind('region_definition', function ($value) {
			$path = Region::locateDefinitionOrFail($value);
			return Region::fromDefinitionFile($path);
		});
		$this->loadRoutesFrom(__DIR__.'/routes.php');
	}

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $this->registerEloquentFactoriesFrom( __DIR__ . '/Database/factories');
    }

	/**
	 * Register factories.
	 *
	 * @param  string  $path
	 * @return void
	 * @see https://github.com/laravel/framework/issues/11881#issuecomment-261688266
	 */
	protected function registerEloquentFactoriesFrom($path)
	{
		$this->app->make(Factory::class)->load($path);
	}
}
