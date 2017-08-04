<?php

namespace App\Models\APICommands;

use App\Models\Revision;
use DB;
use App\Models\Page;
use App\Models\PageContent;
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
     * @return Page The newly added page.
     */
    public function execute($input, Authenticatable $user)
    {
        $page = null;
        DB::beginTransaction();
        $parent = Page::find($input['parent_id']);
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
        }
        $pagecontent = PageContent::create([
            'title' => $input['title'],
            'site_id' => $parent->site_id,
            'options' => [],
            'layout_name' => $input['layout']['name'],
            'layout_version' => $input['layout']['version']
        ]);
        $revision = Revision::createFromPageContent($pagecontent, $user);
        $revision->save();
        $page->setDraft($revision);
        $page->save();
        DB::commit();
        return $page;
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
                'exists:pages,id'
             ],
            // if before_id exists it must have the parent_id specified for the new route / page.
            'before_id' => [
                'nullable',
                Rule::exists('pages','id')
                    ->where('parent_id', $data->get('parent_id'))
            ],
            'slug' => [
                // slug is required and can only contain lowercase letters, numbers, hyphen or underscore.
                'required',
                'regex:/^[a-z0-9_-]+$/',
                // there must not be an existing draft route with the same slug under the parent page
                Rule::unique('pages', 'slug')
                   ->where('parent_id', $data->get('parent_id'))
                   ->whereNotNull('draft_id'),
            ],
            'layout.name' => [
                'string',
                'max:100',
                'required'
            ],
            'layout.version' => [
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