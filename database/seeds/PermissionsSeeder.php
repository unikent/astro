<?php

use Illuminate\Database\Seeder;

use App\Models\Permission;
use App\Models\Role;

class PermissionsSeeder extends Seeder
{
	/**
	 * Seeds the database with permissions.
	 * Run on its own with 'php artisan db:seed --class=PermissionsSeeder'
	 * *Warning* Do not use this seed to simply rename roles, this will remove the 
	 * 	original role alltogether and replace it with a new one, meaning that all users 
	 * 	assigned that role will lose it (along with its permissions).
	 *
	 * @return void
	 */
	public function run()
	{
		$roles_and_permissions = [
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
		
		$role_ids = [];

		foreach ($roles_and_permissions as $role_name => $permission_names) {
			
			// retrieve or add the role
			$role = Role::where('name', $role_name)->first();
			if (!$role) {
				$role = new Role(['name' => $role_name]);
				$role->save();
			}

			// then add permissions to the role
			$permission_ids = [];
			foreach ($permission_names as $permission_name) {

				$permision = Permission::where('name', $permission_name)->first();
				if (!$permision) {
					$permision = new Permission(['name' => $permission_name]);
					$permision->save();
				}
				$permission_ids[] = $permision->id;
			}

			$role->permissions()->sync($permission_ids);
			$role_ids[] = $role->id;
		}
		
		// then remove any unspecified roles
		$unspecified_roles = Role::whereNotIn('id', $role_ids)->pluck('id');
		Role::destroy($unspecified_roles);
	}
}
