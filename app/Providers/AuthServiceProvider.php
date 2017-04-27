<?php

namespace App\Providers;

use App\Models\Page;
use App\Models\Site;
use App\Models\Route;
use App\Policies\PagePolicy;
use App\Policies\SitePolicy;
use App\Policies\RoutePolicy;
use Illuminate\Support\Facades\Gate;
use App\Models\Definitions\Block as BlockDefinition;
use App\Models\Definitions\Layout as LayoutDefinition;
use App\Models\Definitions\Region as RegionDefinition;
use App\Policies\Definitions\BlockPolicy as BlockDefinitionPolicy;
use App\Policies\Definitions\LayoutPolicy as LayoutDefinitionPolicy;
use App\Policies\Definitions\RegionPolicy as RegionDefinitionPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
	/**
	 * The policy mappings for the application.
	 *
	 * @var array
	 */
	protected $policies = [
		Page::class => PagePolicy::class,
		Route::class => RoutePolicy::class,
		Site::class => SitePolicy::class,
		BlockDefinition::class => BlockDefinitionPolicy::class,
		LayoutDefinition::class => LayoutDefinitionPolicy::class,
		RegionDefinition::class => RegionDefinitionPolicy::class,
	];

	/**
	 * Register any authentication / authorization services.
	 *
	 * @return void
	 */
	public function boot()
	{
		$this->registerPolicies();

		//
	}
}
