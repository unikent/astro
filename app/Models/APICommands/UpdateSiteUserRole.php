<?php

namespace App\Models\APICommands;

use App\Models\Contracts\APICommand;
use App\Models\User;
use App\Models\Role;
use App\Models\Site;
use App\Models\UserSiteRole;
use DB;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Collection;
use App\Models\Page;

/**
 * Updates the role for a user on a site.
 * If the role is empty / null, ensures the user has no role on the site.
 * @package App\Models\APICommands
 */
class UpdateSiteUserRole implements APICommand
{

    /**
     * Sets the user's role on a site, or removes any existing role if none specified.
     * @param array $input The input options [ 'id => 'site id', 'username' => 'user name', 'role' => 'role name']
     * @return object|Site
     */
    public function execute($input, Authenticatable $user)
    {
        $result = DB::transaction(function() use($input,$user){
            $user = User::where('username', $input['username'])->first();
            $role = empty($input['role']) ? null : Role::where('slug', $input['role'])->first();
            UserSiteRole::where('site_id', '=', $input['id'])
                        ->where('user_id', '=', $user->id)
                        ->delete();
            if($role){
                UserSiteRole::create([
                    'user_id' => $user->id,
                    'site_id' => $input['id'],
                    'role_id' => $role->id
                ]);
            }
            return Site::find($input['id']);
        });
        return $result;
    }



    /**
     * Get the error messages for this command.
     * @param Collection $data The input data for this command.
     * @return array Custom error messages mapping field_name => message
     */
    public function messages(Collection $data, Authenticatable $user)
    {
        return [
		];
    }

    /**
     * Get the validation rules for this command.
     * @param Collection $data The input data for this command.
     * @return array The validation rules for this command.
     */
    public function rules(Collection $data, Authenticatable $user)
    {
        $page = Page::find($data->get('id'));
        $parent_id = $page ? $page->parent_id : null;
        $rules = [
            'id' => [
                'exists:sites'
            ],
            'username' => [
                'exists:users'
            ],
            'role' => [
                'nullable',
                'exists:roles,slug'
            ]
        ];
        return $rules;
    }
}