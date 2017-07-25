<?php

namespace App\Models\APICommands;

use App\Models\Route;
use App\Models\Page;
use App\Models\Contracts\APICommand;
use Illuminate\Validation\Rule;
use Illuminate\Support\Collection;
use Illuminate\Contracts\Auth\Authenticatable;

/**
 * Adding a page is actually adding a page at a certain route.
 * @package App\Models\APICommands
 */
class AddPage implements APICommand
{

    /**
     * Adding a Page
     * @param $input
     * @return mixed
     */
    public function execute($input, Authenticatable $user)
    {
        $parent = Route::find($input['parent_id']);
        $after = Route::find($input['after_id']);
        $route = $parent->getChildWithSlug($input['slug']);
        if($route){
            if($route->hasDraft()){
                throw new DraftExistsException($route);
            }
        }else{
            $route = Route::create(['site_id' => $parent->site_id, 'parent_id' => $parent->id, 'slug' => $input['slug']]);
            if ($after) {
                $route->makeNextSiblingOf($after);
            } else {
                $route->makeChildOf($parent);
            }
        }
        $page = new Page([]);
        $route->setDraft($page);
        $route->save();
        return $route;
    }

    public function messages(Collection $data, Authenticatable $user)
    {
        return [];
    }

    public function rules(Collection $data, Authenticatable $user)
    {
        return [
            'parent_id' => [
                'required',
                'exists:routes,id'
             ],
            // if after_id exists it must have the parent_id specified for the new route / page.
            'after_id' => [
                'nullable',
                Rule::exists('routes','id')
                    ->where('parent_id', $data->get('parent_id'))
            ],
            'slug' => [
                // slug is required and can only contain lowercase letters, numbers, hyphen or underscore.
                'required',
                'regex:/^[a-z0-9_-]+/$',
                // there must not be an existing draft route with the same slug under the parent page
                Rule::unique('routes', 'slug')
                   ->where('parent_id', $data->get('parent_id'))
                   ->whereNotNull('page_id'),
            ],
            'layout_name' => [
                'string',
                'max:100',
                'required'
            ],
            'layout_version' => [
                'integer'
            ],
            'title' => [
                'string',
                'max:150',
                'required'
            ]
        ];
    }
}