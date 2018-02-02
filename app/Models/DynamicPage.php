<?php

namespace App\Models;

/**
 * Provides a read-only Page object with overridable / proxyable Page relations for use in transformers for dynamic pages.
 * @package App\Models
 */
class DynamicPage extends Page
{
	/**
	 * The site for this page.
	 * @var Site|null
	 */
	public $site = null;

	/**
	 * @var Page|null The "real" page which is a parent of this one. May also be a dynamic page.
	 */
	public $parent = null;

	/**
	 * The revision for this page.
	 * @var Revision|null
	 */
	public $revision = null;

	/**
	 * Create a dynamic page. Attributes should be the ones matching the attributes within the Page and Revision models.
	 * Parameters are optional ONLY because the current implementation extending Page which extends Baum means that instances
	 * of this class may be constructed with no parameters in order to use other methods on the class.
	 * @todo implement this differently more loosely coupled from Eloquent
	 * @param Page $parent - The parent of this page.
	 * @param string $slug - The slug for this page.
	 * @param array $attributes - Attributes and values that define this Page and its Revision. The attributes should have
	 * the same name as those within the Page and Revision models, such as layout_name, layout_version, options, blocks, etc.
	 */
	public function __construct(array $attributes = [], $slug = null, Page $parent = null)
	{
		if($parent) {
			$attributes['slug'] = $slug;
			parent::__construct($attributes);
			$this->parent = $parent;
			$this->path = $this->parent->path . (strlen($this->parent->path) > 1 ? '/' : '') . $this->slug;
			$this->site = $this->parent->site;
			$this->parent_id = $this->parent->id;
			$this->depth = $this->parent->depth + 1;
			$this->site_id = $this->parent->site_id;
		}
		$this->revision = new Revision($attributes);
	}

	public function parent() { return $this->parent; }
	public function nextPage() { return null; }
	public function previousPage() { return null; }
	public function getSiblingsWithRevision() { return null; }
	public function getChildrenWithRevision() { return null; }
	public function getRevisions() { return []; }
	public function getAncestorsWithRevision()
	{
		$ancestors = $this->parent->getAncestorsWithRevision();
		$ancestors[] = $this->parent;
		return $ancestors;
	}
}