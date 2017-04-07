<?php

namespace App\Providers;

use Laravel\Dusk\DuskServiceProvider;
use Illuminate\Support\ServiceProvider;
use Barryvdh\LaravelIdeHelper\IdeHelperServiceProvider;

class AppServiceProvider extends ServiceProvider
{
	/**
	 * Bootstrap any application services.
	 *
	 * @return void
	 */
	public function boot()
	{
		//
	}

	/**
	 * Register any application services.
	 *
	 * @return void
	 */
	public function register()
	{
		if($this->app->environment('local', 'testing', 'staging'))
		{
			$this->app->register(DuskServiceProvider::class);
		}

		if($this->app->environment('development'))
		{
			$this->app->register(IdeHelperServiceProvider::class);
		}
	}
}
