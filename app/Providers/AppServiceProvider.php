<?php

namespace App\Providers;

use Laravel\Dusk\DuskServiceProvider;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Validator;
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
		Validator::extend('definition_exists', function ($attribute, $value, $parameters, $validator){
			$data = $validator->getData();

			$name = $value;
			$version = (isset($parameters[1]) && isset($data[$parameters[1]])) ? $data[$parameters[1]] : null;

			$class = $parameters[0];
			return $class::locateDefinition($name, $version) ? true : false;
		});
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
