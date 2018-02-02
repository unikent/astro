<?php

namespace App\Providers;

use App\Models\APICommands\PublishPage;
use App\Models\Definitions\BaseDefinition;
use App\Models\Definitions\SiteDefinition;
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

		/**
		 * Hack to be able to validate a block's combined name and version are in the {name}-v{version}s listed
		 * in the allowedBlocks array of section definitions.
		 * TODO refactor so that block's have a definition_id or type instead of name and version?
		 */
		Validator::extend('inVersioned', function($attr, $value, $parameters){
			$id = $value . '-v' . $parameters[0];
			array_shift($parameters);
			$options = join(',',$parameters);
			return in_array($id, explode(',',$options));
		});

		Validator::extend('slug_unchanged_or_unique', function($attr, $value, $parameters, $validator) {
			$id = isset($parameters[0]) ? $parameters[0] : null;
			$page = Page::find($id);
			if($page) {
				if($page->slug == $value){ // can keep slug the same...
					return true;
				}
				if($page->parent_id){ // can change slug on home page
					$ok = Page::where('parent_id', '=', $page->parent_id)
								->where('version', Page::STATE_DRAFT)
								->where('slug', $value)
								->count() == 0;
					return $ok;
				}
			}
			// technically if a page doesn't exist its path is unique and unchanged
			return true;
		});

		Validator::extend('page_is_a_subpage', function($attr, $id) {
			return Page::where('id', '=', $id)
				->whereNotNull('parent_id')->exists();
		});

		/**
		 * Ensure page being moved has not been published, or that the new parent has been published.
		 */
		Validator::extend('page_is_new_or_new_parent_is_not_new', function($attr, $value, $parameters) {
			$page = Page::find($value);
			$parent = Page::find(!empty($parameters[0]) ? $parameters[0] : null);
			if($page && $parent){
				return !$page->publishedVersion() || $parent->publishedVersion();
			}
		});

		Validator::extend('parent_is_published', function( $attr, $value ) { return PublishPage::canBePublished($value); });

		Validator::extend('page_is_published', function( $attr, $value ) { return  (($page = Page::find($value)) && $page->publishedVersion()); });

		Validator::extend('page_is_draft', function( $attr, $value ) { return ($page = Page::find($value) ) && $page->version == Page::STATE_DRAFT; });

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

		/**
		 * Checks if a requested site template definition exists.
		 * usage in validation rules: ['definition_name_field' => 'site_definition_exists:{version}']
		 */
		Validator::extend('site_definition_exists', function($attribute, $value, $parameters, $validator){
			return SiteDefinition::locateDefinition(SiteDefinition::idFromNameAndVersion($value,$parameters[0]));
		});

		Validator::extend('definition_exists', function ($attribute, $value, $parameters, $validator){
			$data = $validator->getData();

			$name = $value;
			$version = (isset($parameters[1]) && isset($data[$parameters[1]])) ? $data[$parameters[1]] : null;

			$class = $parameters[0];
			return $class::locateDefinition($class::idFromNameAndVersion($name,$version)) ? true : false;
		});

		/**
		 * Refactor this into something that just checks if two rows have a field with identical id... if that doesnt already exist in laravel...
		 */
		Validator::extend('same_site', function($attribute, $value, $parameters, $validator){
			$site_id = DB::table('pages')->where('id', $parameters[0])->value('site_id');
			return DB::table('pages')->where('id', $value)->where('site_id', $site_id)->where('version', Page::STATE_DRAFT)->exists();
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
