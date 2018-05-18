<?php

namespace Tests\Feature\Traits;

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
	 * @param array $pages - Array, starting with homepage defining the pages that are expected to exist in the site.
	 */
	public function assertSiteHasPageStructure($site_id, $structure)
	{
		$site = Site::find($site_id);
		if($site) {
			$pages = $site->draftPages()->toHierarchy()->toArray();
		}
		else {
			return false;
		}
	}

}