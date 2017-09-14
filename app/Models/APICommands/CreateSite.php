<?php

namespace App\Models\APICommands;

use App\Models\Revision;
use App\Models\Site;
use App\Models\RevisionSet;
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
     * @return Site The newly created Site.
     */
    public function execute($input,Authenticatable $user)
    {
        return DB::transaction(function() use($user, $input) {
            $site = Site::create([
                'name' => $input->get('name'),
                'publishing_group_id' => $input->get('publishing_group_id'),
                'host' => $input->get('host'),
                'path' => $input->get('path'),
                'options' => []
            ]);
            $layout = $input->get('homepage_layout');
            $this->createHomePage($site, 'Home Page', $layout, $user);
            $site->refresh();
            return $site;
        });
    }

    /**
     * Create the home page for a site.
     * @param string $title The title for the homepage for this site.
     * @param array $layout The layout for the homepage for this site [name => '', version => '']
     * @param Authenticatable $user The creator of this site.
     * @return Page Newly created Homepage
     */
    public function createHomePage($site, $title, $layout, $user)
    {
        $page = Page::create([
            'site_id' => $site->id,
            'parent_id' => null,
            'version' => Page::STATE_DRAFT,
            'slug' => null,
            'created_by' => $user->id,
            'updated_by' => $user->id
        ]);
        $revision_set = RevisionSet::create(['site_id' => $site->id]);
        $revision = Revision::create([
            'revision_set_id' => $revision_set->id,
            'title' => $title,
            'created_by' => $user->id,
            'updated_by' => $user->id,
            'layout_name' => $layout['name'],
            'layout_version' => $layout['version']
        ]);
        $page->setRevision($revision);
        $page->refresh();
        return $page;
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
        $layout = $data->get('homepage_layout', []);
        $version = !empty($layout['version']) ? $layout['version'] : null;
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
                'unique:sites,path,null,id,host,' . $data->get('host'),
                'unique_site_path:' . $data->get('host')
            ],
            'homepage_layout.name' => [
                'required',
                'string',
                'max:100',
                'regex:/^[a-z0-9_.-]+$/i',
                'layout_exists:' . $version
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