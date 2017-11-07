<?php

namespace App\Models\APICommands;

use App\Models\Contracts\APICommand;
use App\Models\Revision;
use DB;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Collection;
use App\Models\Page;

/**
 * Updates the slug for a page.
 * Updates the path for the page and all of its descendants.
 * Also does this for the published version(s) of the page and its descendants.
 * @package App\Models\APICommands
 */
class UpdatePageSlug implements APICommand
{

	/**
	 * Carry out the command, based on the provided $input.
	 * @param array $input The input options as key=>value pairs.
	 * @return mixed
	 */
	public function execute($input, Authenticatable $user)
	{
		$result = DB::transaction(function () use ($input, $user) {
			$page = Page::find($input['id']);
			if ($input['slug'] == $page->slug) {
				return $page;
			}
			// update published version of page if there is one
			// need to do this BEFORE updating the draft page, as we can only find the published version
			// by looking for a page with the same path / slug
			$published = $page->publishedVersion();
			if($published){
				$this->updateSlugAndPaths($published, $input['slug']);
			}
			$this->updateSlugAndPaths($page, $input['slug']);
			$page->refresh();
			return $page;
		});
		return $result;
	}


	/**
	 * Update the paths in the database for a Page and its descendants when its slug changes.
	 * @param Page $page The Page which is moving
	 * @param string $slug The new slug for the Page.
	 */
	public function updateSlugAndPaths($page, $slug)
	{
		$replace_length = strlen($page->path) + 1; // get length to replace
		$replace_with = (strlen($page->parent->path) == 1 ? '' : $page->parent->path) . '/' . $slug;
		$binds = [
			// handle root parent with path of '/' as a special case
			'prefix' => $replace_with,
			'replace_length' => $replace_length,
			'lft' => $page->lft,
			'rgt' => $page->rgt,
			'site_id' => $page->site_id,
			'version' => $page->version
		];

		DB::update("
			UPDATE pages
			SET path = CONCAT(:prefix, SUBSTRING(path, :replace_length))
			WHERE lft >= :lft
			AND lft < :rgt
			AND site_id = :site_id
			AND version = :version
			",
			$binds
		);
		$page->slug = $slug;
		$page->save();
	}

	/**
	 * Get the error messages for this command.
	 * @param Collection $data The input data for this command.
	 * @return array Custom error messages mapping field_name => message
	 */
	public function messages(Collection $data, Authenticatable $user)
	{
		return [
			'id.required' => 'Cannot update a page\'s slug without knowing its id.',
			'id.page_is_a_subpage' => 'Cannot update the slug of a page that doesn\'t exist or is a homepage.',
			'slug.regex' => 'Slug can only contain lowercase letters, numbers and hyphens.',
			'slug_unchanged_or_unique' => 'A page with the slug "' . $data->get('slug') . '" already exists at this level.'
		];
	}

	/**
	 * Get the validation rules for this command.
	 * @param Collection $data The input data for this command.
	 * @return array The validation rules for this command.
	 */
	public function rules(Collection $data, Authenticatable $user)
	{
		$rules = [
			'id' => [
				'required',
				'page_is_a_subpage',
				'page_is_draft:' . $data->get('id')
			],
			'slug' => [
				// slug is required and can only contain lowercase letters, numbers, hyphen or underscore.
				'required',
				'regex:/^[a-z0-9_-]+$/',
				// there must not be an existing draft page with the same slug under the parent page
				'slug_unchanged_or_unique:' . $data->get('id')
			],
		];
		return $rules;
	}
}
