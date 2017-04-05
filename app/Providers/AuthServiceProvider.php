<?php

namespace App\Providers;

use Illuminate\Support\Facades\Gate;
use App\Models\Definitions\Block as BlockDefinition;
use App\Policies\Definitions\BlockPolicy as BlockDefinitionPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
	/**
	 * The policy mappings for the application.
	 *
	 * @var array
	 */
	protected $policies = [
		BlockDefinition::class => BlockDefinitionPolicy::class,
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
