<?php

namespace App\Models\APICommands;

use DB;
use App\Models\Page;
use App\Models\PageContent;
use App\Models\Contracts\APICommand;
use Illuminate\Validation\Rule;
use Illuminate\Support\Collection;
use Illuminate\Contracts\Auth\Authenticatable;

/**
 * Moving a Page always moves its subpages with it.
 * @package App\Models\APICommands
 */
class MovePage implements APICommand
{

    /**
     * Moving a Page
     * @param $input
     * @return mixed
     */
    public function execute($input, Authenticatable $user)
    {
        return DB::transaction(function() use($input,$user){
            $page = Page::find($input['page_id']);
            $parent = Page::find($input['parent_id']);
            // we only move Pages which have drafts
            if (!$page->hasDraft()) {
                throw new DraftRequiredException($page);
            }
            $result = $page->move($parent);
            return $result;
        });
    }

    public function messages(Collection $data, Authenticatable $user)
    {
        return [
            'parent_id.same_site' => 'The parent must be in the same site.',
            'parent_id.exists' => 'Parent not foundy',
            'parent_id.required' => 'Where my parent?',
            'parent_id.descendant_or_self' => 'huh?'
        ];
    }

    public function rules(Collection $data, Authenticatable $user)
    {
        return [
            'page_id' => [
                'required',
                'exists:pages,id'
            ],
            // parent must exist and be in the same site as this page
            'parent_id' => [
                'required',
                'exists:pages,id',
                'same_site:' . $data->get('page_id'),
                'not_descendant_or_self:'.$data->get('page_id')
             ],
            // if before_id exists it must have the parent_id specified for the new route / page.
//            'next_id' => [
//                'nullable',
//                Rule::exists('pages','id')->where(function($query) use($data) {
//                    $query->where('parent_id', $data->get('parent_id'));
//                })
//            ],

        ];
    }
}