<?php

namespace App\Models\APICommands;

use App\Events\PageEvent;
use App\Models\Definitions\Layout;
use App\Models\Definitions\SiteDefinition;
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
	use AddsPagesTrait;

    /**
     * Carry out the command, based on the provided $input.
     * @param Collection $input The input options as key=>value pairs.
     * @param Authenticatable $user
     * @return Site The newly created Site.
     */
    public function execute($input,Authenticatable $user)
    {
        return DB::transaction(function() use($user, $input) {
        	$site_definition = $input->get('site_definition');
            $site = Site::create([
                'name' => $input->get('name'),
                'host' => $input->get('host'),
                'path' => $input->get('path'),
                'site_definition_name' => $site_definition['name'],
                'site_definition_version' => $site_definition['version'],
                'options' => []
            ]);
            $template =
				SiteDefinition::fromDefinitionFile(SiteDefinition::locateDefinition(
					SiteDefinition::idFromNameAndVersion($site_definition['name'],$site_definition['version'])
				));

            $layout = $template->defaultPages['layout'];
            // layout can be {name}-v{version} here in which case we convert to ['name' => '...', 'version' => '...']
            if(!is_array($layout)){
            	$layout = SiteDefinition::idToNameAndVersion($layout);
			}
            $homepage = $this->createHomePage($site, 'Home', $layout, $user);
            if(!empty($template->defaultPages['children'])){
				$this->addPages($homepage, $template->defaultPages['children'], $user);
			}
            $site->refresh();
            return $site;
        });
    }


	/**
	 * Add a hierarchy of pages to a site.
	 * @param Page $parent - The parent page to add subpages to.
	 * @param array $tree array of pages attributes, each of which may have a
	 * 						children array containing subpage definitions.
	 * Required attributes are:
	 * - slug
	 * - title
	 * - layout['name']
	 * - layout['version']
	 */
    public function addPages($parent, $pages, Authenticatable  $user)
	{
		foreach($pages as $definition) {
			$layout = is_array($definition['layout']) ?
								$definition['layout'] :
								Layout::idToNameAndVersion($definition['layout']);
			$added = $this->addPage(
				$parent,
				$definition['slug'],
				$definition['title'],
				$user,
				$layout['name'],
				$layout['version']
			);
			if(!empty($definition['children'])){
				$this->addPages($added, $definition['children'], $user);
			}
		}
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
		event(new PageEvent(PageEvent::CREATING, null, [
			'parent' => null,
			'layout_name' => $layout['name'],
			'layout_version' => $layout['version'],
			'slug' => null,
			'title' => $title,
			'user' => $user
		]));
        $page = Page::create([
            'site_id' => $site->id,
            'parent_id' => null,
            'version' => Page::STATE_DRAFT,
            'slug' => null,
            'created_by' => $user->id,
            'updated_by' => $user->id
        ]);
		$page->createDefaultBlocks($layout['name'], $layout['version']);
		$revision_set = RevisionSet::create(['site_id' => $site->id]);
        $revision = Revision::create([
            'revision_set_id' => $revision_set->id,
            'title' => $title,
            'created_by' => $user->id,
            'updated_by' => $user->id,
            'layout_name' => $layout['name'],
            'layout_version' => $layout['version'],
			'valid' => true
        ]);
        $page->setRevision($revision);
        $page->refresh();
		event(new PageEvent(PageEvent::CREATED, $page, null));
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
        $definition = $data->get('site_definition', []);
        $version = !empty($definition['version']) ? $definition['version'] : null;
        $rules = [
            'name' => ['required', 'max:190' ],
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
            'site_definition.name' => [
                'required',
                'string',
                'max:100',
                'regex:/^[a-z0-9_.-]+$/i',
                'site_definition_exists:' . $version
            ],
            'site_definition.version' => [
                'required',
                'integer'
            ]
        ];
        return $rules;
    }
}
