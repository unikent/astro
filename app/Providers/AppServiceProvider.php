<?php

namespace App\Providers;

use App\Models\APICommands\PublishPage;
use App\Models\Page;
use DB;
use Laravel\Dusk\DuskServiceProvider;
use Illuminate\Support\Facades\Schema;
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
		Schema::defaultStringLength(191); // Fix for the webfarm running older MySQL

        Validator::extend('parent_is_published', function( $attr, $value ) { return PublishPage::parentIsPublished($value); });

		Validator::extend('definition_exists', function ($attribute, $value, $parameters, $validator){
			$data = $validator->getData();

			$name = $value;
			$version = (isset($parameters[1]) && isset($data[$parameters[1]])) ? $data[$parameters[1]] : null;

			$class = $parameters[0];
			return $class::locateDefinition($name, $version) ? true : false;
		});

        /**
         * Refactor this into something that just checks if two rows have a field with identical id... if that doesnt already exist in laravel...
         */
		Validator::extend('same_site', function($attribute, $value, $parameters, $validator){
		    $site_id = DB::table('pages')->where('id', $parameters[0])->value('site_id');
		    return DB::table('pages')->where('id', $value)->where('site_id', $site_id)->exists();
        });

        /**
         */
		Validator::extend('not_descendant_or_self', function($attribute, $value, $parameters, $validator){
		     $page = Page::find($parameters[0]);
		     if(!$page){
		         return false;
             }
             $is = !$page->descendantsAndSelf()->where('id', $value)->exists();
		     return $is;
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
