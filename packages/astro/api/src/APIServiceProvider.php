<?php

namespace Astro\API;

use Astro\API\Console\Commands\AddSite;
use Astro\API\Console\Commands\AddUser;
use Astro\API\Console\Commands\CheckDefns;
use Astro\API\Console\Commands\ManageAdmins;
use Astro\API\Console\Commands\SetupPermissions;
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
		}
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }
}
