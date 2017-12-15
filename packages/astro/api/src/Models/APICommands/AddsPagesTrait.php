<?php

namespace Astro\API\Models\APICommands;

use Astro\API\Models\Definitions\Layout;
use Astro\API\Models\Page;
use Astro\API\Models\Revision;
use Astro\API\Models\RevisionSet;
use Illuminate\Contracts\Auth\Authenticatable;

/**
 * Functionality to add a page as a subpage of an existing page.
 * @package Astro\API\Models\APICommands
 */
trait AddsPagesTrait
{
	/**
	 * Adds a new page (creating a revision) at the end of this page's children.
	 * @param Page $parent The Page object which will be a parent to this page.
	 * @param string $slug The slug for the new page, must not already exist under this parent.
	 * @param string $title The title for the new page.
	 * @param Authenticatable $user The user account to set as the creator.
	 * @param string $layout_name The name of the layout for this page.
	 * @param int $layout_version The version of the layout for this page.
	 * @return Page - The newly added page.
	 */
	public function addPage($parent, $slug, $title, $user, $layout_name, $layout_version)
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
		$page->createDefaultBlocks($layout_name, $layout_version);
		$revision_set = RevisionSet::create(['site_id' => $parent->site_id]);
		$revision = Revision::create([
			'revision_set_id' => $revision_set->id,
			'title' => $title,
			'created_by' => $user->id,
			'updated_by' => $user->id,
			'layout_name' => $layout_name,
			'layout_version' => $layout_version,
			'blocks' => $page->bake(Layout::idFromNameAndVersion($layout_name, $layout_version)),
			'options' => null,
			'valid' => true
		]);
		$page->setRevision($revision);
		return $page;
	}
}