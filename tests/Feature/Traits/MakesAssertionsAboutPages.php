<?php

namespace Tests\Feature\Traits;

use App\Models\Page;

/**
 * Methods to make assertions about pages and their relationships with other models.
 * Uses knowledge of the underlying database model so that the actual tests don't need to.
 * @package Tests\Feature\Traits
 */
trait MakesAssertionsAboutPages
{
	/**
	 * Is a page the last child of another page.
	 * @param int $page_id - The page id to test.
	 * @param int $parent_id - The id of the expected parent page.
	 * @return bool - True if the page is the last child of the parent page otherwise false, also false if page or parent doesn't exist
	 */
	public function pageIsLastChildOf($page_id, $parent_id)
	{
		$parent = Page::find($parent_id);
		if($parent) {
			$lastChild = $parent->children()->get()->last();
			return $lastChild && $lastChild->id == $page_id;
		}
		return false;
	}

	/**
	 * Is a page a child of another page.
	 * @param int $page_id - Id of the page.
	 * @param int $parent_id - Id of the parent page.
	 * @return bool - True if both pages exist and the page is a child of the parent, otherwise false.
	 */
	public function pageIsChildOf($page_id, $parent_id)
	{
		return $parent_id &&
				Page::where('id', $page_id)
					->where('parent_id', $parent_id)
					->exists();
	}

	/**
	 * Is a page the first child of another page.
	 * @param int $page_id - Id of the page.
	 * @param int $parent_id - Id of the parent page.
	 * @return bool - True if both pages exist and the page is the first child of the parent, otherwise false.
	 */
	public function pageIsFirstChildOf($page_id, $parent_id)
	{
		$parent = Page::find($parent_id);
		if($parent) {
			$firstChild = $parent->children->first();
			return $firstChild && $firstChild->id == $page_id;
		}
		return false;
	}

	/**
	 * Is a page the next sibling (in order) of another page.
	 * @param int $page_id - Id of the page.
	 * @param int $previous_sibling_id - Id of the page that the page is the next sibling of.
	 * @return bool - True if both pages exist and the page is the next sibling of the page, otherwise false.
	 */
	public function pageIsNextSiblingOf($page_id, $previous_sibling_id)
	{
		$page = Page::find($page_id);
		return $page &&
			   $page->previousPage() &&
				$page->previousPage()->id == $previous_sibling_id;
	}

	/**
	 * Is a page the previous sibling (in order) of another page.
	 * @param int $page_id - Id of the page.
	 * @param int $next_sibling_id - Id of the page that the page is the previous sibling to.
	 * @return bool - True if both pages exist and the page is the previous sibling of the page, otherwise false.
	 */
	public function pageIsPreviousSiblingOf($page_id, $next_sibling_id)
	{
		$page = Page::find($page_id);
		return $page &&
			$page->nextPage() &&
			$page->nextPage()->id == $next_sibling_id;

	}

	/**
	 * Are two pages siblings (different pages with same parent)
	 * @param int $page_id - Id of one of the pages.
	 * @param int $sibling_id - Id of the other page
	 * @return bool - True if both pages exist and have the same (non-null) parent, otherwise false.
	 */
	public function pagesAreSiblings($page_id, $sibling_id)
	{
		$page1 = Page::find($page_id);
		$page2 = Page::find($sibling_id);
		return ($page1 && $page2 && $page1->parent_id == $page2->parent_id && $page1->parent_id);
	}

}