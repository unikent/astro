<?php

namespace App\Providers;

use App\Models\Role;
use App\Models\Site;
use App\Models\Page;
use App\Models\User;
use App\Models\Media;
use App\Models\Permission;
use App\Policies\PagePolicy;
use App\Policies\RolePolicy;
use App\Policies\SitePolicy;
use App\Policies\UserPolicy;
use App\Policies\MediaPolicy;
use App\Services\Auth\JwtGuard;
use App\Policies\PermissionPolicy;
use Illuminate\Support\Facades\Auth;
use App\Models\Definitions\SiteDefinition;
use App\Policies\Definitions\SiteDefinitionPolicy;
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

        Auth::extend('jwt', function ($app, $name, array $config) {
            return new JwtGuard(Auth::createUserProvider($config['provider']), $app->make('request'));
        });
	}
}
