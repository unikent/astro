<?php

namespace App\Providers;

use Astro\API\Models\Permission;
use Astro\API\Models\Role;
use Astro\API\Models\Site;
use Astro\API\Models\Page;
use Astro\API\Models\Media;
use App\Models\User;
use Astro\API\Policies\PagePolicy;
use Astro\API\Policies\PermissionPolicy;
use Astro\API\Policies\RolePolicy;
use Astro\API\Policies\SitePolicy;
use Astro\API\Policies\MediaPolicy;
use Astro\API\Models\Definitions\Block as BlockDefinition;
use Astro\API\Models\Definitions\Layout as LayoutDefinition;
use Astro\API\Models\Definitions\Region as RegionDefinition;
use Astro\API\Models\Definitions\SiteDefinition;
use Astro\API\Policies\Definitions\BlockPolicy as BlockDefinitionPolicy;
use Astro\API\Policies\Definitions\LayoutPolicy as LayoutDefinitionPolicy;
use Astro\API\Policies\Definitions\RegionPolicy as RegionDefinitionPolicy;
use Astro\API\Policies\Definitions\SiteDefinitionPolicy;
use Astro\API\Policies\UserPolicy;
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
		Site::class => SitePolicy::class,
		Media::class => MediaPolicy::class,
		BlockDefinition::class => BlockDefinitionPolicy::class,
		LayoutDefinition::class => LayoutDefinitionPolicy::class,
		RegionDefinition::class => RegionDefinitionPolicy::class,
		SiteDefinition::class => SiteDefinitionPolicy::class,
		User::class => UserPolicy::class,
		Permission::class => PermissionPolicy::class,
		Role::class => RolePolicy::class
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
