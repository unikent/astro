<?php

namespace App\Models\APICommands;

use App\Models\Definitions\Contracts\Definition;
use App\Models\Definitions\Layout;
use App\Models\Revision;
use DB;
use App\Models\Block;
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
class AddPage implements APICommand
{

    /**
     * Adding a Page
     * @param $input
     * @return Page The newly added page.
     */
    public function execute($input, Authenticatable $user)
    {
        return DB::transaction(function() use($input, $user){
            $parent = Page::find($input['parent_id']);
            $page = $this->addChild($parent,
                $input['slug'],
                $input['title'],
                $user,
                $input['layout']['name'],
                $input['layout']['version']
            );
            if(!empty($input['next_id'])){
                $page->makePreviousSiblingOf(Page::find($input['next_id']));
            }
            return $page;
        });
    }

	/**
	 * Create and save all the default blocks / regions / sections for a Page based on a layout.
	 * @param Page $page - The Page object to create blocks for.
	 * @param string $layout_name - The name of the layout
	 * @param integer $layout_version - The version of the layout
	 */
    public function createDefaultBlocks($page, $layout_name, $layout_version)
	{
		$layout_definition = Layout::fromDefinitionFile(Layout::locateDefinition($layout_name,$layout_version));
		if($layout_definition){
			$data = $layout_definition->getDefaultPageContent();
			$this->saveBlocks($page->id, $data);
		}
	}

	/**
	 * Saves the default content for the page defined in blocks to the database.
	 *
	 * @param array $data ['region-name' => [['name' => 'section-1-name', 'blocks' => [ ... [block def 1], [block def 2]...]],...]]
	 */
	public function saveBlocks($page_id,$data)
	{
		foreach($data as $region_name => $sections){
			foreach($sections as $section){
				foreach($section['blocks'] as $i => $block_data) {
					$block = new Block;
					$block->fill($block_data);
					$block->page_id = $page_id;
					$block->order = $i;
					$block->region_name = $region_name;
					$block->section_name = $section['name'];
					$block->save();
				}
			}
		}
	}

    /**
     * Adds a new page (creating a revision) at the end of this page's children.
     * @param string $slug The slug for the new page, must not already exist under this parent.
     * @param string $title The title for the new page.
     * @param Authenticatable $user The user account to set as the creator.
     * @param string $layout_name The name of the layout for this page.
     * @param int $layout_version The version of the layout for this page.
     * @return Page
     */
    public function addChild($parent, $slug, $title, $user, $layout_name, $layout_version)
    {
        $page = $parent->children()->create(
            [
                'site_id' => $parent->site_id,
                'version' => Page::STATE_DRAFT,
                'slug' => $slug,
                'parent_id' => $parent->id,
                'created_by' => $user->id,
                'updated_by' => $user->id
            ]
        );
        $this->createDefaultBlocks($page,$layout_name, $layout_version);
        $revision_set = RevisionSet::create(['site_id' => $parent->site_id]);
        $revision = Revision::create([
            'revision_set_id' => $revision_set->id,
            'title' => $title,
            'created_by' => $user->id,
            'updated_by' => $user->id,
            'layout_name' => $layout_name,
            'layout_version' => $layout_version,
            'blocks' => $page->bake(),
            'options' => null,
			'valid' => true
        ]);
        $page->setRevision($revision);
        return $page;
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
            'parent_id' => [
                'required',
                'exists:pages,id',
				'page_is_draft:'.$data->get('parent_id')
             ],
            // if next_id exists it must have the parent_id specified for the new route / page.
            'next_id' => [
                'nullable',
                Rule::exists('pages','id')
                    ->where( function($query) use($data) { $query->where('parent_id', $data->get('parent_id'));})
            ],
            'slug' => [
                // slug is required and can only contain lowercase letters, numbers, hyphen or underscore.
                'required',
                'regex:/^[a-z0-9_-]+$/',
                // there must not be an existing draft page with the same slug under the parent page
                Rule::unique('pages', 'slug')
                   ->where('parent_id', $data->get('parent_id'))
                   ->where('version', Page::STATE_DRAFT),
            ],
            'layout.name' => [
                'string',
                'max:100',
                'required',
                'regex:/^[a-z0-9_.-]+$/i',
                'layout_exists:' . $version            ],
            'layout.version' => [
                'required',
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