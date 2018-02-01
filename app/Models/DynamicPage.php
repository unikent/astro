<?php

namespace App\Models;

/**
 * Provides a read-only Page object with overridable / proxyable Page relations for use in transformers for dynamic pages.
 * @package App\Models
 */
class DynamicPage extends Page
{
	public $site = null;
	public $parent = null;
	public $revision = null;

	/**
	 * @var Page|null The "real" page which is a parent of this one.
	 */
	protected $dynamicParent = null;

	/**
	 * DynamicPage constructor. Sets attributes based on
	 * @param Page $parent
	 * @param array $attributes
	 */
	public function __construct(array $attributes = [], Page $parent = null)
	{
		if($parent) {
			parent::__construct($attributes);
			$this->dynamicParent = $parent;
			$this->parent = $parent;
			$this->path = $this->parent->path . (strlen($this->parent->path) > 1 ? '/' : '') . $this->slug;
			$this->site = $this->dynamicParent->site;
			$this->parent_id = $this->parent->id;
			$this->depth = $this->parent->depth + 1;
			$this->site_id = $this->parent->site_id;
		}
		$this->revision = new Revision($attributes);
	}

	public function __get($what)
	{
		switch( $what ) {
			case 'site':
				return $this->dynamicParent->site;
				break;

		}
		return parent::__get($what);
	}

	public function parent() { return $this->parent; }
	public function nextPage() { return null; }
	public function previousPage() { return null; }
	public function getSiblingsWithRevision() { return null; }
	public function getChildrenWithRevision() { return null; }
	public function getRevisions() { return []; }
	public function getAncestorsWithRevision()
	{
		$ancestors = $this->dynamicParent->getAncestorsWithRevision();
		$ancestors[] = $this->dynamicParent;
		return $ancestors;
	}
}