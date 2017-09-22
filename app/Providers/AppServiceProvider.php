<?php

namespace App\Providers;

use App\Models\APICommands\PublishPage;
use App\Models\Page;
use App\Validation\Rules\LayoutExistsRule;
use App\Validation\Rules\UniqueSitePathRule;
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

        Validator::extend('slug_unchanged_or_unique', function($attr, $value, $parameters, $validator) {
			$id = isset($parameters[0]) ? $parameters[0] : null;
			$page = Page::find($id);
			if($page ){
				if($page->slug == $value){ // can keep slug the same...
					return true;
				}
				if($page->parent_id){ // can change slug on home page
					$ok = Page::where('parent_id', '=', $page->parent_id)
								->where('slug', $value)
								->count() == 0;
					return $ok;
				}
			}
			return false;
        });

        Validator::extend('parent_is_published', function( $attr, $value ) { return PublishPage::canBePublished($value); });

        Validator::extend('page_is_valid', function($attr, $id) {
        	$page = Page::find($id);
        	return $page && $page->revision->valid;
		});

        Validator::extend('unique_site_path', function($attr, $value, $parameters, $validator) {
            $host = isset($parameters[0]) ? $parameters[0] : null;
            return (new UniqueSitePathRule($host))->passes($attr, $value);
        });
        Validator::extend('layout_exists', function($attribute, $value, $parameters, $validator){
           return (new LayoutExistsRule(empty($parameters[0]) ? 0 : $parameters[0]))->passes($attribute,$value);
        });

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
