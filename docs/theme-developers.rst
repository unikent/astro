Developing Themes, Definitions, Blocks, Layouts & Fields
========================================================

This guide is aimed at developers creating their own themes, layouts, blocks and fields separate from the Astro
core.

Custom Fields
-------------

Astro includes a number of fields by default. It is not currently possible to create new fields without editing
Astro directly.


Definitions
-----------

Definitions from multiple repositories can be used in the same instance of Astro.

The definitions available to the Editor are defined at build time, whilst those for the

The Editor, API and Renderer all need to be configured with which repositories should be used, and all definitions should be namespaced.

Renderer
........

The renderer expects

The API
.......

Editor
......

Blocks
......

Each Block __may__ define a class implementing Astro\Renderer\Contracts\Block which will be available when rendering the block.

Any business logic or data-retrieval too complex to include in the template should be performed here.

If no class is defined then an instance of Astro\Renderer\API\Base\Block will be used instead.

These classes are __always__ stored in the block directory for that block, with a classname matching
the block name and version, and should be named as "Block.php". They do not need to be autoloadable.

Layout, Page & Site Options
---------------------------

Layouts can specify options in a similar manner to blocks. Each option can be marked as available at "page", "site",
or both levels.

* Page-only options can be specified by editors on a per-page basis.
* Site-only options can be specified by administrators on a per-site basis, and are available to all layouts on all pages.
* Both-level options can have a default value specified by administrators on a per-site basis, which can be overridden
  on a per-page basis by editors.
* If a page-level or both-level option is marked as inheritable, then a page can use the value specified for an ancestor page
  instead of setting its own value.

Blocks with Dynamic Data
------------------------

Blocks may require dynamic data in both the Renderer and the Editor:

The Renderer
............

To support form posting or dynamic updates using ajax, the renderer must provide blocks with the ability to request
data. Any request to /valid/url/ajax/block/[operation] will result in the renderer:

* Loading the requested route / page / layout definitions as usual for that route
* Creating the block object (if a block class exists)
* Calling the block object's ajax() method.
* Returning the output to the browser.

It is up to the blocks (and their templates) whether what is returned is json data or pure html.

The API
.......

The API has its own /ajax endpoint. As with the editor it will initialise the relevant block class (if it exists)
and call its ajaxAdmin() method. The block can assume that the user making this request has been authenticated and
is authorised by the API to make requests for this site and block.

The Editor
..........

Blocks may need to make ajax requests for data under two circumstances:

* Requesting data for dymanic fields, for example a select dropdown to choose a blog category.
* Dynamically updating their displayed content.



