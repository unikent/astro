<?php

namespace App\Events;

use App\Models\Page;

/**
 * A PageEvent is triggered whenever a page is about to change or has changed.
 * The type of event can be determined from the $type attribute.
 * The Page in its current state can be retrieved from the $page attribute. This will be the previous state of the
 * page for ...ing events (e.g. creating) and the modified state of the page for '...ed' events (e.g. created).
 * @package App\Events
 */
class PageEvent
{
	const PAGE_EVENT_WILDCARD = self::class;
	const CREATING = 'astro.page.creating';
	const CREATED = 'astro.page.created';
	const UPDATING = 'astro.page.updating';
	const UPDATED = 'astro.page.updated';
	const DELETING = 'astro.page.deleting';
	const DELETED = 'astro.page.deleted';
	const PUBLISHING = 'astro.page.publishing';
	const PUBLISHED = 'astro.page.published';
	const UNPUBLISHING = 'astro.page.unpublishing';
	const UNPUBLISHED = 'astro.page.unpublished';
	const MOVING = 'astro.page.moving';
	const MOVED = 'astro.page.moved';
	const RENAMING = 'astro.page.renaming';
	const RENAMED = 'astro.page.renamed';
	const OPTIONS_UPDATING = 'astro.page.options_updating';
	const OPTIONS_UPDATED = 'astro.page.options_updated';

	/**
	 * @var string The type of page change (create, update, etc)
	 */
	public $type;

	/**
	 * @var Page The Page data.
	 */
	public $page;

	/**
	 * @var array The changed data
	 */
	public $changes;

	/**
	 * PageEvent constructor.
	 * @param string $type - The type of page
	 * @param Page $page - The Page that has changed (in its current state)
	 * @param array $changes - The data that has been changed (varies depending on event type)
	 */
	public function __construct($type, $page, $changes)
	{
		$this->type = $type;
		$this->page = $page;
		$this->changes = $changes;
	}
}