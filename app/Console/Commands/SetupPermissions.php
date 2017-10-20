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
								{--old-slug=}
								{--new-slug=}
								{--new-name=}
								';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'A tool to set up roles and permissions';

	public static $role_names = [
		Role::OWNER => 'Owner',
		Role::EDITOR => 'Editor',
		Role::CONTRIBUTOR => 'Contributor'
	];

	public static $permission_names = [
		Permission::CREATE_SUBSITE => 'Create Subsites',
		Permission::EDIT_SUBSITE => 'Edit Subsites',
		Permission::DELETE_SUBSITE => 'Delete Subsites',
		Permission::EDIT_MENU => 'Edit Menus',
		Permission::MOVE_SUBSITE => 'Move Subsites',
		Permission::ADD_PAGE => 'Add Pages',
		Permission::EDIT_PAGE => 'Edit Pages',
		Permission::DELETE_PAGE => 'Delete Pages',
		Permission::MOVE_PAGE => 'Move Pages',
		Permission::ADD_IMAGE => 'Add Images',
		Permission::EDIT_IMAGE => 'Edit Images',
		Permission::USE_IMAGE => 'Use Images',
		Permission::PUBLISH_PAGE => 'Publish Pages',
		Permission::PREVIEW_PAGE => 'Preview Pages',
		Permission::APPROVAL => 'Approve Pages',
		Permission::ASSIGN_SITE_PERMISSIONS => 'Assign Site Permissions',
		Permission::ASSIGN_PAGE_PERMISSIONS => 'Assign Page Permissions',
		Permission::ASSIGN_SUBSITE_PERMISSIONS => 'Assign Subsite Permissions',
		Permission::CREATE_SITE => 'Create Sites',
		Permission::DELETE_SITE => 'Delete Sites',
		Permission::EDIT_SITE => 'Edit Sites',
		Permission::MOVE_SITE => 'Move Sites',
		Permission::TEMPLATE_MANIPULATION => 'Manipulate Templates'
	];

	/**
	 * Defines the roles and their permission.
	 *
	 * @var string
	 */
	public static $roles_and_permissions = [

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
				if ($this->option('old-slug') && $this->option('new-slug') && $this->option('new-name')) {
					$this->renameRole($this->option('old-slug'), $this->option('new-slug'), $this->option('new-name'));
				}
				else {
					$this->error('You need to specify the --old-slug, --new-slug and --new-name for the role you\'d like to edit');
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

		foreach (self::$roles_and_permissions as $role_slug => $permission_slugs) {
			
			// retrieve or add the role
			$role = Role::where('slug', $role_slug)->first();
			if (!$role) {
				$role = new Role(['slug' => $role_slug, 'name' => self::$role_names[$role_slug]]);
				$role->save();
				$this->info('New role added: ' . $role->name);
			}
			// then add permissions to the role
			$role_permission_ids = [];
			foreach ($permission_slugs as $permission_slug) {

				$permission = Permission::where('slug', $permission_slug)->first();
				if (!$permission) {
					$permission = new Permission(['slug' => $permission_slug, 'name' => self::$permission_names[$permission_slug]]);
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

	public function renameRole($old_slug, $new_slug, $new_name)
	{
		$role = Role::where('slug', $old_slug)->first();
		if ($role) {
			$role->name = $new_name;
			$role->slug = $new_slug;
			$role->save();
			$this->info('Role has been renamed from "' . $old_slug . '" to "' . $new_slug . '" ("'. $new_name .'")');
		}
		else
			$this->warn('Role "' . $old_slug . '" could not be found');

	}
}
