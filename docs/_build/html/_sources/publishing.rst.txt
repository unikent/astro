
Astro Functionality
###################

Sites
#####

* Each site has a domain name and a root URL
* Multiple sites can have the same domain name, but:

  * Root URL must be different for each
  * A site cannot have any pages under the root URL of another site.

* Sites can only be created and modified by administrators.
* Each site belongs to a Publishing Group.
* Only members of that publishing group can edit pages within the site.
* The layouts available to a site should be selected by the administrator when creating the site.

Adding a Site
=============

Administrators add a site using a form, specifying:

* Site Title
* Site Domain Name
* Site URL
* Site Publishing Group
* Site Description
* Available layout templates.

Modifying a Site
================

Existing sites can only be modified by administrators.

Site Meta
~~~~~~~~~

Administrators can change settings including the title, description and publishing group for a site.

Changing the URL or Domain Name
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Administrators can change URL and domain name of a site if this does not clash with the site structure of any
other existing site.

Changing the layouts available
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Administrators can change the selection of layouts which are available to a site, but must specify an alternative for
any layout currently in use by any existing pages.

Deleting a Site
===============

???

Publishing
##########

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

When adding a page, a template (layout) must be selected from those available for the site using the `template picker`_.

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

Changing a page's template
~~~~~~~~~~~~~~~~~~~~~~~~~~

Users can change the template associated with a page using the `template picker`_. If both old and new templates contain
regions with the same name then any blocks within these regions will be retained. Blocks within regions not in the
new template will be discarded.

Changing a page's URL / slug
~~~~~~~~~~~~~~~~~~~~~~~~~~~~

The part of the page url after the last forward slash is called the page's "slug". Users can edit this.

Editing a page's meta
~~~~~~~~~~~~~~~~~~~~~

A page may have metadata such as title, description, etc associated with it.
Users can edit this.

Previewing Changes
==================

When editing a page, users can click the "Preview Page" button to view the preview the currently saved draft version of that page.
From that page they will be able to browse the rest of the site in preview-mode. Only authenticated users in the publishing
group for a page can view the preview version of the page / site.

Published Page Revisions
~~~~~~~~~~~~~~~~~~~~~~~~

A copy of every published version of each page is stored. The user can access this list of revisions, view them and make
one the current draft version of the page.

Listing Revisions
~~~~~~~~~~~~~~~~~

The listing of revisions for a page includes:
* The title of the page when it was published.
* The date and time the page was published.
* A link or button to view the revision.
* A link or button to replace the current draft page with the revision.

Viewing a Revision
~~~~~~~~~~~~~~~~~~

When the user clicks to view a revision of a page that page is opened up in a new browser tab. The URL to view the
revision can be shared but will only be accessible by authenticated users in the publishing group for that page.

Reverting to a Revision
~~~~~~~~~~~~~~~~~~~~~~~

When the user clicks to revert to a previous revision they should be prompted to confirm they want to override the
current draft version of the page. Any work they have in draft mode will be lost.

View Unpublished Changes
========================

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
not be updated **unless** the page being published has been moved or deleted, as these actions cascade. A page can
only be published if its parent page is published.

Publish a Page & Subpages
~~~~~~~~~~~~~~~~~~~~~~~~~

Users can publish a single page and its subpages by selecting the "Publish -> This page & Subpages" option.

Publish a selection of pages
~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Users can publish a selection of pages by repeatedly following the steps to publish a single page or a single page
and its subpages.

Unpublish a Page
~~~~~~~~~~~~~~~~

Users can "unpublish" a page (set it to draft). This will also set all subpages of that page to draft mode.

UI Components
#############

The main UI components used within the Editor are:

Media Manager
=============

The media manager is used to manage all uploaded media (images, pdfs, etc) within Astro.

Visibility and editability of a media item can be restricted by site or publishing group.

Media can be tagged and can be searched by tag.

Media can be cropped within the media manager.

Media can be viewed as thumbnails or as a detailed list.

Page Selector
=============

When adding a link to an existing page, users can use the page selector to select that page.

The page selector displays all the pages in the current site.

Perhaps it should display all pages in all sites?

When users move a page, all links to that page should be updated.

Template Picker
===============

The template picker allows a user to select a template to use for a page from the list of available templates.

Each template is displayed with:
* A thumbnail image.
* A title.
* A description.
* A list of "do's and don'ts" for when to use this template.

The template picker should scroll appropriately if more templates are available than can be displayed at once.

The template picker includes recently used templates at the top.

