<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

use App\Models\Permission;
use App\Models\Role;

class SetupPermissions extends Command
{
	/**
	 * The name and signature of the console command.
	 *
	 * @var string
	 */
	protected $signature = 'astro:permissions
								{action : The action to perform this could either be "refresh" or "rename-role"}
								{--old-name=}
								{--new-name=}
								';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'A tool to set up roles and permissions';


	/**
	 * Defines the roles and their permission.
	 *
	 * @var string
	 */
	public static $roles_and_permissions = [
		Role::ADMIN => [
			Permission::CREATE_SUBSITE,
			Permission::EDIT_SUBSITE,
			Permission::DELETE_SUBSITE,
			Permission::EDIT_MENU,
			Permission::MOVE_SUBSITE,
			Permission::ADD_PAGE,
			Permission::EDIT_PAGE,
			Permission::DELETE_PAGE,
			Permission::MOVE_PAGE,
			Permission::ADD_IMAGE,
			Permission::EDIT_IMAGE,
			Permission::USE_IMAGE,
			Permission::PUBLISH_PAGE,
			Permission::PREVIEW_PAGE,
			Permission::APPROVAL,
			Permission::ASSIGN_SITE_PERMISSIONS,
			Permission::ASSIGN_PAGE_PERMISSIONS,
			Permission::ASSIGN_SUBSITE_PERMISSIONS,
			Permission::CREATE_SITE,
			Permission::DELETE_SITE,
			Permission::EDIT_SITE,
			Permission::MOVE_SITE,
			Permission::TEMPLATE_MANIPULATION
		],
		Role::OWNER => [
			Permission::CREATE_SUBSITE,
			Permission::EDIT_SUBSITE,
			Permission::DELETE_SUBSITE,
			Permission::EDIT_MENU,
			Permission::MOVE_SUBSITE,
			Permission::ADD_PAGE,
			Permission::EDIT_PAGE,
			Permission::DELETE_PAGE,
			Permission::MOVE_PAGE,
			Permission::ADD_IMAGE,
			Permission::EDIT_IMAGE,
			Permission::USE_IMAGE,
			Permission::PUBLISH_PAGE,
			Permission::PREVIEW_PAGE,
			Permission::APPROVAL,
			Permission::ASSIGN_SITE_PERMISSIONS,
			Permission::ASSIGN_PAGE_PERMISSIONS,
			Permission::ASSIGN_SUBSITE_PERMISSIONS
		],
		Role::EDITOR => [
			Permission::EDIT_SUBSITE,
			Permission::EDIT_MENU,
			Permission::MOVE_SUBSITE,
			Permission::ADD_PAGE,
			Permission::EDIT_PAGE,
			Permission::DELETE_PAGE,
			Permission::MOVE_PAGE,
			Permission::ADD_IMAGE,
			Permission::EDIT_IMAGE,
			Permission::USE_IMAGE,
			Permission::PUBLISH_PAGE,
			Permission::PREVIEW_PAGE,
			Permission::APPROVAL
		],
		Role::CONTRIBUTOR => [
			Permission::EDIT_SUBSITE,
			Permission::EDIT_PAGE,
			Permission::ADD_IMAGE,
			Permission::EDIT_IMAGE,
			Permission::USE_IMAGE,
			Permission::PUBLISH_PAGE,
			Permission::PREVIEW_PAGE,
			Permission::APPROVAL
		]
	];

	/**
	 * Create a new command instance.
	 *
	 * @return void
	 */
	public function __construct()
	{
		parent::__construct();
	}

	/**
	 * Execute the console command.
	 *
	 * @return mixed
	 */
	public function handle()
	{
		switch ($this->argument('action')) {
			case 'refresh':
				$this->refreshAllRolesAndPermissions();
				break;
			case 'rename-role':
				if ($this->option('old-name') && $this->option('new-name')) {
					$this->renameRole($this->option('old-name'), $this->option('new-name'));
				}
				else {
					$this->error('You need to specify the --old-name and --new-name for the role you\'d like to edit');
				}
				break;
			default:
				$this->error('Invalid action specified.');
				break;
		}
	}

	public function refreshAllRolesAndPermissions($value='')
	{	
		$role_ids = [];
		$permission_ids = [];

		foreach (self::$roles_and_permissions as $role_name => $permission_names) {
			
			// retrieve or add the role
			$role = Role::where('name', $role_name)->first();
			if (!$role) {
				$role = new Role(['name' => $role_name]);
				$role->save();
				$this->info('New role added: ' . $role->name);
			}
			// then add permissions to the role
			$role_permission_ids = [];
			foreach ($permission_names as $permission_name) {

				$permission = Permission::where('name', $permission_name)->first();
				if (!$permission) {
					$permission = new Permission(['name' => $permission_name]);
					$permission->save();
					$this->info('New permision added: ' . $permission->name);
				}
				$role_permission_ids[] = $permission->id;
				$permission_ids[] = $permission->id;
			}

			$role->permissions()->sync($role_permission_ids);
			$role_ids[] = $role->id;
		}
		
		// then remove any unspecified roles
		$unspecified_roles = Role::whereNotIn('id', $role_ids)->pluck('id')->toArray();
		if (!empty($unspecified_roles)) {
			Role::destroy($unspecified_roles);
			$this->info('Role ids removed: ' . implode(', ', $unspecified_roles));
		}

		// also remove any unspecified permissions
		$unspecified_permissions = Permission::whereNotIn('id', $permission_ids)->pluck('id')->toArray();
		if (!empty($unspecified_permissions)) {
			Permission::destroy($unspecified_permissions);
			$this->info('Permission ids removed: ' . implode(', ', $unspecified_permissions));
		}
	}

	public function renameRole($old_name, $new_name)
	{
		$role = Role::where('name', $old_name)->first();
		if ($role) {
			$role->name = $new_name;
			$role->save();
			$this->info('Role has been renamed from "' . $old_name . '" to "' . $new_name . '"');
		}
		else
			$this->warn('Role "' . $old_name . '" could not be found');

	}
}
