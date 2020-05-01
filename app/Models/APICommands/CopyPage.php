<?php

namespace App\Models\APICommands;

use App\Events\PageEvent;
use App\Models\Revision;
use DB;
use App\Models\Page;
use App\Models\RevisionSet;
use App\Models\Contracts\APICommand;
use Illuminate\Validation\Rule;
use Illuminate\Support\Collection;
use Illuminate\Contracts\Auth\Authenticatable;

/**
 * Adding a page is actually adding a page at a certain route.
 * @package App\Models\APICommands
 */
class CopyPage implements APICommand
{
	use AddsPagesTrait;

    /**
     * Adding a Page
     * @param $input
     * @return Page The newly added page.
     */
    public function execute($input, Authenticatable $user)
    {
        return DB::transaction(function() use($input, $user){
            $page = Page::find($input['id']);
            $parent = $page->parent_id ? Page::find($page->parent_id) : $page;
			$newPage = $this->addPage($parent,
                $input['new_slug'],
                $input['new_title'],
                $user,
                $page->revision->layout_name,
                $page->revision->layout_version
            );
			return $newPage;
        });
    }

    public function messages(Collection $data, Authenticatable $user)
    {
        return [];
    }

    public function rules(Collection $data, Authenticatable $user)
    {
        $layout = $data->get('layout', []);
        $version = !empty($layout['version']) ? $layout['version'] : null;

        return [
            'new_slug' => [
                // slug is required and can only contain lowercase letters, numbers, hyphen or underscore.
                'required',
                'regex:/^[a-z0-9_-]+$/',
                // there must not be an existing draft page with the same slug under the parent page
                Rule::unique('pages', 'slug')
                   ->where('parent_id', $data->get('parent_id'))
                   ->where('version', Page::STATE_DRAFT),
            ],
            'new_title' => [
                'string',
                'max:150',
                'required'
            ]
        ];
    }
}