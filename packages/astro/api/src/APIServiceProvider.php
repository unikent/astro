<?php

namespace Astro\API;

use Astro\API\Console\Commands\AddSite;
use Astro\API\Console\Commands\AddUser;
use Astro\API\Console\Commands\CheckDefns;
use Astro\API\Console\Commands\ManageAdmins;
use Astro\API\Console\Commands\SetupPermissions;
use Illuminate\Database\Eloquent\Factory;
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
        //$this->loadRoutesFrom(__DIR__.'/routes.php');
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
