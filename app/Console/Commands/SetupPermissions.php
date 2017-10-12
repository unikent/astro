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
		'Admin' => [
			'Create Subsite',
			'Edit Subsite',
			'Delete Subsite',
			'Edit Menu',
			'Move Subsite',
			'Add Page',
			'Edit Page',
			'Delete Page ',
			'Move Page',
			'Add Image',
			'Edit Image',
			'Use Image',
			'Publish Page',
			'Preview page',
			'Approval',
			'Assign Sites permissions',
			'Assign page permisions',
			'Assign Subsite permissions',
			'Create Site',
			'Delete Site',
			'Edit Site',
			'Move Site',
			'Template Manipulation'
		],
		'Site Owner' => [
			'Create Subsite',
			'Edit Subsite',
			'Delete Subsite',
			'Edit Menu',
			'Move Subsite',
			'Add Page',
			'Edit Page',
			'Delete Page ',
			'Move Page',
			'Add Image',
			'Edit Image',
			'Use Image',
			'Publish Page',
			'Preview page',
			'Approval',
			'Assign Sites permissions',
			'Assign page permisions',
			'Assign Subsite permissions'
		],
		'Editor' => [
			'Edit Subsite',
			'Edit Menu',
			'Move Subsite',
			'Add Page',
			'Edit Page',
			'Delete Page ',
			'Move Page',
			'Add Image',
			'Edit Image',
			'Use Image',
			'Publish Page',
			'Preview page',
			'Approval'
		],
		'Contributor' => [
			'Edit Subsite',
			'Edit Page',
			'Add Image',
			'Edit Image',
			'Use Image',
			'Publish Page',
			'Preview page',
			'Approval'
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

		foreach (self::$roles_and_permissions as $role_name => $permission_names) {
			
			// retrieve or add the role
			$role = Role::where('name', $role_name)->first();
			if (!$role) {
				$role = new Role(['name' => $role_name]);
				$role->save();
				$this->info('New role added: ' . $role->name);
			}
			// then add permissions to the role
			$permission_ids = [];
			foreach ($permission_names as $permission_name) {

				$permision = Permission::where('name', $permission_name)->first();
				if (!$permision) {
					$permision = new Permission(['name' => $permission_name]);
					$permision->save();
					$this->info('New permision added: ' . $role->name);
				}
				$permission_ids[] = $permision->id;
			}

			$role->permissions()->sync($permission_ids);
			$role_ids[] = $role->id;
		}
		
		// then remove any unspecified roles
		$unspecified_roles = Role::whereNotIn('id', $role_ids)->pluck('id');
		if (!$unspecified_roles->isEmpty()) {
			Role::destroy($unspecified_roles);
			$this->info('Role ids removed: ' . implode(', ', $unspecified_roles));
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
