Publishing In Astro
###################

* Publishers always edit, move and delete pages in draft mode.
* Changes must be published for them to take effect on the live site.
* A page can be published **without** also publishing changes to its subpages, with the exception of
  moves and deletes which always cascade.

* When editing a page, until that page is published, only the latest edits to the draft are saved.
* Once a page is published, the published version is always available in the revisions history.


Managing Pages
==============

Site Hierarchy
~~~~~~~~~~~~~~

The site navigation view lists all pages of the site as a hierarchical structure. The view can be filtered as:

Draft View
----------

This shows the current "draft" version of the site, ie. what it would look like if ALL current changes were published.

Published View
--------------

This shows the current "live" version of the site.

Deleted View
------------

This view shows all pages and subpages that have been deleted, but not yet published.

Unpublished View
----------------

This view displays all modifications that have not yet been published.

Combined view
-------------

This view is similar to draft view with the exceptions that:

* Unpublished deletions are visible (highlighted)
* The current (original) location of moved pages are shown in relation to their new (unpublished) positions.

Adding a Page
~~~~~~~~~~~~~

Editors can add a page before, after or as a child page of any existing page.

When adding a page, a template (layout) must be selected from those available for the site.

Users will be prompted to enter a reason for the page's existence.

Deleting a Page
~~~~~~~~~~~~~~~

When a page is deleted all its child pages will also be deleted.

Pages are not deleted from the live site until the deletion is "published".

Pending-deletes can be cancelled by the user, but pages in this state cannot be moved.

Deleting a page can be initiated with the delete button within the editor for that page.

Moving a Page
~~~~~~~~~~~~~

Moving a page also moves all its child pages (they are still located under it).

Moved pages must be "published" for the move to be live.

Publishing a moved page also moves all its child pages but **does not automatically publish** any draft version of those pages.

A page can **not** be moved to be a child page of itself or any of its descendants.

Copying a Page
~~~~~~~~~~~~~~

A page can be copied **with or without** its child pages.

Template Picker
===============

The template picker allows a user to select a template to use for a page from the list of available templates.

Each template is displayed with:
* A thumbnail image.
* A title.
* A description.
* A list of "do's and don'ts" for when to use this template.

Changing a page's template
~~~~~~~~~~~~~~~~~~~~~~~~~~

Users can change the template associated with a page using the template picker. If both old and new templates contain
regions with the same name then any blocks within these regions will be retained. Blocks within regions not in the
new template will be discarded.

The Editor
==========

Users may be making simple, quickly published updates to a page, or might be working on one or more sets of pages
over a number of months. Multiple users may be responsible for the same page or set of pages, and due to leave,
working patterns or other circumstances may not know what their colleagues have worked on.

Viewing all unpublished changes makes it clearer what has changed and what can be published.

Updated Pages
~~~~~~~~~~~~~

The updated pages view shows all pages which have been edited, moved, created or deleted without those changes
being published.

Moved Pages
~~~~~~~~~~~

The moved pages view shows only those pages and subpages which have been moved without having those changes published.

Deleted Pages
~~~~~~~~~~~~~

The deleted pages view shows only those pages and subpages which have been deleted without that being published.

Publishing a Page
=================

Publishing a page is the action resulting in any changes to a page being replicated to the live site.

Changes include one or more of:

* Modifications to the page content.
* Modifications to the page meta data.
* Moving the page.
* Deleting the page.

Publishing may be initiated via a button on the editor for a single page, from within the **unpublished pages** or
from the page navigation.

When publishing, users should enter a brief summary of what is being published, moved or deleted which will be
visible in the activity log for the site.

Publish a Single Page
~~~~~~~~~~~~~~~~~~~~~

Users can publish a single page by selecting the "Publish" -> "This Page Only" option. Any modified subpages will
not be updated **unless** the page being published has been moved or deleted, as these actions cascade.

Publish a Page & Subpages
~~~~~~~~~~~~~~~~~~~~~~~~~

Users can publish a single page and its subpages by selecting the "Publish -> This page & Subpages" option.

Publish a selection of pages
~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Users can publish a selection of pages by repeatedly following the steps to publish a single page or a single page
and its subpages.

Previewing Changes
==================

Published Page Revisions
~~~~~~~~~~~~~~~~~~~~~~~~

Listing Revisions
~~~~~~~~~~~~~~~~~

Viewing a Revision
~~~~~~~~~~~~~~~~~~

Reverting to a Revision
~~~~~~~~~~~~~~~~~~~~~~~

Managing Media
==============

