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
 * It also always moves any published version of the page and subpages.
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
			$next_sibling = Page::find(!empty($input['next_id']) ? $input['next_id'] : null);

			$old_parent_id = $page->parent_id;
			// if we need to move published version, then we should get it before updating paths
			// as the only way we can identify published version is by shared paths.
			$published_page = $page->publishedVersion();

			// now we update the paths if we have moved rather than just reordered the page.
			if ($parent->id != $old_parent_id) {
				if( $published_page ){
					$published_parent = $parent->publishedVersion();
					$this->updatePaths($published_page, $published_parent);
				}
				$this->updatePaths($page, $parent);
			}

			// moving or reordering (baum operations only)

			// if we are moving it before a page...
			if ($next_sibling) {
				$page->makePreviousSiblingOf($next_sibling);
				if($published_page){
					// if the next page has been published, we can just move the published version before that too
					$next_copy = $next_sibling;
					// it is possible that the next page we have moved before has not itself been published, and
					// that therefore we cannot move our published page to "before" the published version of it, as it
					// does not exist.
					// if so, we try to find the first of its following siblings which has been published to move the
					// published version before.
					// and if that fails, we just move it to the end of the published parent's children.
					while($next_copy && !$next_copy->publishedVersion()) {
						$next_copy = $next_copy->nextPage();
					}
					if($next_copy){
						$published_page->makePreviousSiblingOf($next_copy->publishedVersion());
					}
					else{
						$published_page->makeLastChildOf($parent->publishedVersion());
					}
				}
			} 

			// otherwise just add to end of parent
			else {
				$page->makeLastChildOf($parent);
				// and if a published version exists, move it to the end of the
				// parent's published version...
				if($published_page){
					$published_page->makeLastChildOf($parent->publishedVersion());
				}
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
			'id.page_is_new_or_new_parent_is_not_new' => 'A previously published page cannot be moved into an unpublished parent',
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
				'page_is_draft:' . $data->get('id'),
				'page_is_new_or_new_parent_is_not_new:'.$data->get('parent_id')
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