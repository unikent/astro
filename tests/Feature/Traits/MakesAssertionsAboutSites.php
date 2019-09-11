<?php

namespace Tests\Feature\Traits;

use App\Models\Page;
use App\Models\Site;

/**
 * Methods to make assertions about sites and their relationships with other models.
 * Uses knowledge of the underlying database model so that the actual tests don't need to.
 * @package Tests\Feature\Traits
 */
trait MakesAssertionsAboutSites
{
	/**
	 * Check if a site exists with the given host and path
	 * @param string $host - The domain name for the site
	 * @param string $path - The path for the site.
	 * @return bool True if the site exists in the database, otherwise false
	 */
	public function siteExistsWithHostAndPath($host, $path)
	{
		return !!(Site::where('host','=', $host)
					->where('path', '=', $path)
					->first());
	}

	/**
	 * Check if a site with the given id exists.
	 * @param int $site_id - The id of the site to check for.
	 * @return bool - Returns true if the site exists in the database, otherwise false.
	 */
	public function siteExists($site_id)
	{
		return !!(Site::find($site_id));
	}

	/**
	 * Checks that a site has pages matching the given structure.
	 * @param int $site_id - The Id of the site to check.
	 * @param array $pages - Array in the format expected for default pages in a site definition, starting with homepage,
	 * defining the pages that are expected to exist in the site.
	 * @version string - 'draft' or 'published' to restrict to that version of the site (should not make a difference?)
	 */
	public function assertSiteHasPageStructure($site_id, $structure, $version = Page::STATE_DRAFT)
	{
		$site = Site::find($site_id);
		$pages = $site->pages($version)->with('revision')->get()->toHierarchy()->toArray();
		$match_error = $this->pagesMatch($structure, $pages);
		if($match_error) {
			$this->fail(__METHOD__ . ' site page structure not as expected: ' . $match_error);
		}
	}

	/**
	 * Compare two arrays of pages
	 * @param array $one - Expected page structure, matching format used in site definitions for defaultPages
	 * @param array $two - Actual page structure from site, including revision data (for page title and layout version)
	 * @return bool - True if the page structure is the same, otherwise false.
	 */
	public function pagesMatch($one, $two)
	{
		$two = array_values($two);
		if(count($one) != count($two)) {
			return 'different number of pages: ' . count($one) . ' vs ' . count($two);
		}
		for($i = 0; $i < count($one); $i++) {
			$p1 = $one[$i];
			$p2 = $two[$i];
			if($p1['slug'] != $p2['slug']) {
				return "slug {$p1['slug']} != {$p2['slug']}";
			}
			if($p1['title'] != $p2['revision']['title']) {
				return "title {$p1['title']} != {$p2['revision']['slug']}";
			}
			if($p1['layout'] != $p2['revision']['layout_name'] . '-v' . $p2['revision']['layout_version']) {
				return "layout {$p1['layout_name']} != {$p2['revision']['layout_name']}-v{$p2['revision']['layout_version']}";
			}
			return $this->pagesMatch(array_key_exists('children', $p1) ? $p1['children'] : [], $p2['children']);
		}
		return null;
	}

}