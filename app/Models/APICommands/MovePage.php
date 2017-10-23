<?php

namespace App\Models\APICommands;

use DB;
use App\Models\Page;
use App\Models\Redirect;
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
		return DB::transaction(function () use ($input, $user) {
			$page = Page::find($input['id']);
			$parent = Page::find($input['parent_id']);
			$next = Page::find(!empty($input['next_id']) ? $input['next_id'] : null);
			if ($parent->id != $page->parent_id) {
				$redirects = $this->getRedirects($page);
				if ($redirects) {
					Redirect::insert($redirects);
				}
				$this->updatePaths($page, $parent);
			}
			if ($next) {
				$page->makePreviousSiblingOf($next);
			} else {
				$page->makeLastChildOf($parent);
			}
			$page->refresh();
			return $page;
		});
	}

	/**
	 * Replace the beginning of an existing path with a different prefix.
	 * @param string $path Current path
	 * @param string $new_prefix New prefix
	 * @param int $trim_count Number of characters to remove from the beginning of the current path.
	 * @return string The new path.
	 */
	public function replacePath($path, $new_prefix, $trim_count)
	{
		return $new_prefix . substr($path, $trim_count);
	}

	/**
	 * Get Redirects to create for the moved page and all its descendants.
	 * @param Page $page The Page that will be moved.
	 * @return array of arrays with old path and page ids
	 */
	public function getRedirects($page)
	{
		$redirects = [];
		$remove_length = strlen($page->parent->path);
		foreach ($page->getDescendantsAndSelf() as $item) {
			$redirects[] = [
				'path' => $item->path,
				'page_id' => $page->id //$this->replacePath($item->path, $parent->path , $remove_length)
			];
		}
		return $redirects;
	}

	/**
	 * Update the paths in the database for a Page and its descendants when it moves to a new parent.
	 * @param Page $page The Page which is moving
	 * @param Page $parent The Page which will be the new parent.
	 */
	public function updatePaths($page, $parent)
	{
		$len = strlen($page->parent->path);
		$prefix_length = $len == 1 ? 1 : $len + 1;
		$binds = [
			// handle root parent with path of '/' as a special case
			'prefix' => strlen($parent->path) == 1 ? '' : $parent->path,
			'prefix_length' => $prefix_length,
			'lft' => $page->lft,
			'rgt' => $page->rgt,
			'site_id' => $page->site_id,
			'version' => $page->version
		];

		DB::update("
          UPDATE pages 
          SET 
            path = CONCAT(:prefix, SUBSTRING(path, :prefix_length) )
          WHERE lft >= :lft
          AND lft < :rgt
          AND site_id = :site_id
          AND version = :version
        ",
			$binds
		);
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
			'id' => [
				'required',
				'page_is_draft:' . $data->get('id')
			],
			// parent must exist and be in the same site as this page
			'parent_id' => [
				'required',
				'exists:pages,id',
				'page_is_draft:' . $data->get('parent_id'),
				'same_site:' . $data->get('id'),
				'not_descendant_or_self:' . $data->get('id')
			],
			// if next_id exists it must have the parent_id specified for the new route / page.
			'next_id' => [
				'nullable',
				Rule::exists('pages', 'id')->where(function ($query) use ($data) {
					$query->where('parent_id', $data->get('parent_id'));
				})
			],

		];
	}
}