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
        $page = null;
        return false;
        DB::beginTransaction();
        $parent = Page::find($input['parent_id']);
        $before = !empty($input['before_id']) ? Page::find($input['before_id']) : null;
        $page = $parent->getChildWithSlug($input['slug']);
        if ($page) {
            if ($page->hasDraft()) {
                throw new DraftExistsException($page);
            }
        } else {
            $page = $parent->children()->create([
                'site_id' => $parent->site_id,
                'slug' => $input['slug'],
                'parent_id' => $parent->id
            ]);
            if ($before) {
                $page->makePreviousSiblingOf($before);
            }
        }
        $pagecontent = PageContent::create([
            'title' => $input['title'],
            'site_id' => $parent->site_id,
            'options' => [],
            'layout_name' => $input['layout_name'],
            'layout_version' => $input['layout_version']
        ]);
        $page->setDraft($pagecontent);
        $page->save();
        DB::commit();
        return $pagecontent;
    }

    public function messages(Collection $data, Authenticatable $user)
    {
        return [];
    }

    public function rules(Collection $data, Authenticatable $user)
    {
        return [
            'page_id' => [
                'required',
                'exists:page_content,id'
            ],
            // parent must exist and be in the same site as this page
            'parent_id' => [
                'required',
                'exists:pages,id',
                'same_site:' . $data->get('page_id'),
                'descendant_or_self:'.$data->get('page_id').',false'
             ],
            // if before_id exists it must have the parent_id specified for the new route / page.
            'next_id' => [
                'nullable',
                Rule::exists('pages','id')
                    ->where('parent_id', $data->get('parent_id'))
            ],

        ];
    }
}