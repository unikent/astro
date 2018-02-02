<?php

namespace App\Models\APICommands;

use App\Exceptions\UnpublishedParentException;
use App\Models\Contracts\APICommand;
use App\Models\Page;
use DB;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Collection;

/**
 * Publish a page and (optionally) all its descendants.
 * @package App\Models\APICommands
 */
class PublishPage implements APICommand
{
	/**
	 * Determines if this page can be published or not. If page has a parent, then that must be published first.
	 * @param int $page_id The id of an unpublished page to test.
	 */
	public static function canBePublished($page_id)
	{
		$page = Page::draft()->find($page_id);
		if (!$page) {
			return false;
		}
		if (!$page->parent) {
			return true;
		} else {
			return $page->parent->publishedVersion();
		}
	}

	/**
	 * Carry out the command, based on the provided $input.
	 * @param array $input The input options as key=>value pairs.
	 * @return mixed
	 */
	public function execute($input, Authenticatable $user)
	{
		return DB::transaction(function () use ($input) {
			$page = Page::find($input['id']);

			// is there already a published page at this path?
			$published_page = $page->publishedVersion();

			if (!$published_page) {
				// does this page have a parent, and if so, has it been published?
				$parent = $page->parent;
				$published_parent = null;
				if ($parent) {
					$published_parent = $parent->publishedVersion();
					if (!$published_parent) {
						throw new UnpublishedParentException('Parent pages must be published first.');
					}
				}

				$fields = [
					'site_id' => $page->site_id,
					'version' => Page::STATE_PUBLISHED,
					'parent_id' => $published_parent ? $published_parent->id : null,
					'slug' => $page->slug,
					'created_by' => $page->created_by,
					'updated_by' => $page->updated_by
				];
				if ($published_parent) {
					// publish a subpage
					$published_page = $published_parent->children()->create($fields);
				} else {
					// publish the home page
					$published_page = Page::create($fields);
				}
			}

			$published_page->setRevision($page->revision);

			return $published_page;
		});
	}

	/**
	 * Create an array representing the tree of pages to add to the published page.
	 * @param $pages The pages to copy.
	 * @param string $state
	 * @return array
	 */
	public function copyPages($pages, $state = Page::STATE_PUBLISHED)
	{
		$data = [];
		foreach ($pages as $page) {
			$data[] = [
				'site_id' => $page->site_id,
				'version' => $state,
				'slug' => $page->slug,
				'created_by' => $page->created_by,
				'updated_by' => $page->updated_by,
				'revision_id' => $page->revision_id
			];
		}
		return $data;
	}

	/**
	 * Get the error messages for this command.
	 * @param Collection $data The input data for this command.
	 * @return array Custom error messages mapping field_name => message
	 */
	public function messages(Collection $data, Authenticatable $user)
	{
		return [
			'id.exists' => 'The page does not exist.',
			'id.page_is_draft' => 'You can only publish draft pages.',
			'id.parent_is_published' => 'The parents of this page must be published first.',
			'id.page_is_valid' => 'You cannot publish a page with validation errors.'
		];
	}

	/**
	 * Get the validation rules for this command.
	 * @param Collection $data The input data for this command.
	 * @return array The validation rules for this command.
	 */
	public function rules(Collection $data, Authenticatable $user)
	{
		return [
			'id' => [
				'exists:pages,id',
				'page_is_draft:' . $data->get('id'),
				'parent_is_published:' . $data->get('id'),
				'page_is_valid'
			],
		];
	}
}