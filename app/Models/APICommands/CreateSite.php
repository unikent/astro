<?php

namespace App\Models\APICommands;

use App\Models\Revision;
use App\Models\Site;
use App\Models\PageContent;
use App\Models\Page;
use DB;
use App\Models\Contracts\APICommand;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Contracts\Auth\Authenticatable;

class CreateSite implements APICommand
{

    /**
     * Carry out the command, based on the provided $input.
     * @param Collection $input The input options as key=>value pairs.
     * @param Authenticatable $user
     * @return mixed
     */
    public function execute($input,Authenticatable $user)
    {
        $site = new Site([
            'name' => $input->get('name'),
            'publishing_group_id' => $input->get('publishing_group_id'),
            'host' => $input->get('host'),
            'path' => $input->get('path'),
            'options' => []
        ]);
        DB::transaction(function() use($site, $user, $input) {
            $site->save();
            $layout = $input->get('homepage_layout');
            $site->createHomePage('Home Page', $layout, $user);
        });
        $site->refresh();
        return $site;
    }

    /**
     * Get the error messages for this command.
     * @param Collection $data The input data for this command.
     * @return array Custom error messages mapping field_name => message
     */
    public function messages(Collection $data, Authenticatable $user)
    {
        return [
            'host.unique' => '',
            'path.unique' => 'A site with this host and path already exists.'
        ];
    }

    /**
     * Get the validation rules for this command.
     * @param Collection $data The input data for this command.
     * @return array The validation rules for this command.
     */
    public function rules(Collection $data, Authenticatable $user)
    {
        if(is_null($data->get('path'))){
            $data->put('path','');
        }
        $rules = [
            'name' => ['required', 'max:190' ],
            'publishing_group_id' => [ 'required' ],
            'host' => [
                'required',
                'max:100',
                'regex:/^[a-z0-9.-]+(:[0-9]+)?$/',
                'unique:sites,host,null,id,path,' . $data->get('path')
            ],
            'path' =>[
                'nullable',
                'regex:/^(\/[a-z0-9_-]+)*$/i',
                'unique:sites,path,null,id,host,' . $data->get('host')
            ],
            'homepage_layout.name' => [
                'required',
                'string',
                'max:100',
                'regex:/^[a-z0-9_.-]+$/i'
            ],
            'homepage_layout.version' => [
                'required',
                'integer'
            ]
        ];
        $rules['publishing_group_id'][] = Rule::exists('publishing_groups', 'id');
        return $rules;
    }
}