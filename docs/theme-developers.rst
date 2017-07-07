Developers Guide
================

This guide is aimed at developers creating their own themes, layouts, blocks and fields for use with Astro.

Glossary
--------

Components
..........

* A **Site** consists of one or more *Pages* organised hierarchically.
* Each **Page** has a *Layout* (HTML template) which can contain multiple content *Blocks*
  ordered inside one or more *Regions*.
* **Regions** are areas in an HTML template which can contain certain types of content *Block*. e.g.:

  * The "main" region where most of the content goes.
  * A "left-sidebar" region for navigation, highlighted content blocks, etc.
  * A "header" region.

* **Blocks** are the main user-editable component of Astro and are created by developers and designers
  to represent the various design patterns available to users of the system, for example:

  * A slideshow block where users can choose each image and also specify a caption and URL
  * A plain HTML block, where users can edit basic HTML using a WYSIWYG editor.
  * A product block, displaying information about a product specified by the user.

  *Blocks* are defined with zero or more *Fields*.
* **Fields** represent the user-editable data for each instance of a block. Each *Field* inside a *Block* definition
  has a name, type (e.g. richtext, number, choice), descriptive label and optional validation restrictions and
  supporting data.

User Roles
..........

Developers
~~~~~~~~~~

  * Create *Layout* templates in HTML / PHP / Twig
  * Create *Block* templates in HTML / PHP / Twig
  * Create optional PHP helper classes for each *Layout* or *Block* which requires extra business logic.
  * Creates json-format definition files for each *Layout*, *Block* and *Region*.

Administrators
~~~~~~~~~~~~~~

  * Create Publishing Groups and add Users to them.
  * Create Sites and assign a Publishing Group to each to determine who can edit each site.
  * Specify which Layouts can be used with which Site.

Users or Editors
~~~~~~~~~~~~~~~~

  * Add Pages to Sites they are members of the Publishing Group for.
  * Add and configure the Blocks on each Page.
  * Publish the Pages to the live site once they are ready.

Data
----

*Sites* and *Pages* are stored in the database along with all the Block data for each Page.

The definitions for the Blocks, Regions and Layouts used to create the *Pages* are explained below:

Definitions
-----------

Regions
.......

Regions are defined entirely in a definition.json file stored in a directory named:

``/regions/{REGION_NAME}/V{REGION_VERSION}/definition.json``

.. code-block:: json

 {
    "name": "REGION_NAME",
    "version": "1",
    "blocks": [
        "valid-block-name-1",
        "valid-block-name-2"
    ]
 }

Layouts
.......

Layouts are defined by a json definition file, layout template and vue template all stored in a directory named:

``/layouts/{LAYOUT_NAME}/V{LAYOUT_VERSION}/``

They may optionally have a
``Layout.php`` file containing a Layout class providing additional functionality beyond basic template logic.

The Layout definition json file contains basic information about the Layout including which regions it supports:

``definition.json``

.. code-block:: json

 {
    "name": "LAYOUT_NAME",
    "version": "1",
    "regions": [
       "region-1-name",
       "region-2-name",
    ]
 }

Layouts require two templates:

  * ``template.twig`` - a Twig template that the Renderer will use to render the Layout.
  * ``template.vue`` - A Vue.js template that represents the Layout inside of the Editor.

Example template.vue:
~~~~~~~~~~~~~~~~~~~~~

The Vue.js template must include a <region> component for each region it defines (this is how the editor knows where
to put blocks).

.. literalinclude:: ./examples/simple-vue-layout.vue
   :language: html

The markup **must** be wrapped in ``<template>...</template>`` tags and **must** contain a single root element, eg:

 .. code-block: html

 <template>
   <div>

   </div>
 </template>

Example template.twig:
~~~~~~~~~~~~~~~~~~~~~~

The Twig template should include the logic to render all of the Blocks assigned to the various regions of an
instance of the Layout.

.. literalinclude:: ./examples/simple-twig-layout.twig
   :language: twig

PHP Layout Class
~~~~~~~~~~~~~~~~

The *optional* PHP Layout class should be named ``{LAYOUT_NAME}V{LAYOUT_VERSION}Layout`` and be saved
in a file called ``Layout.php`` in the main directory for that Layout.

It must implement the interface ``Astro\Renderer\Contracts\Layout``:

.. literalinclude:: ../../astro-renderer/src/Contracts/Layout.php
    :language: php



Blocks
......

Similar to Layouts, Blocks are defined by a json definition file, block template and vue template living in a directory
named:

``/blocks/{BLOCK_NAME}/V{BLOCK_VERSION}/``.

They may optionally have a ``Block.php`` file containing a Block
class providing additional functionality beyond basic template logic.

The Block definition file contains basic information about the Block including the *Fields* which comprise its user-editable
data and any restrictions on validation for these fields:

.. code-block:: json

 {
    "name": "BLOCK_NAME",
    "version": "1",
    "label": "Human Readable Block Name",
    "fields": [

    ]
 }

Example template.vue
~~~~~~~~~~~~~~~~~~~~

At a minimum each block's vue template must include:

 # <template>...</template> tags.

.. literalinclude:: ./examples/basic-vue-block.vue
    :language: html

Example PHP Block Class
~~~~~~~~~~~~~~~~~~~~~~~

The *optional* PHP Block class should implement the interface ``Astro\Renderer\Contracts\Block``:

.. literalinclude:: ../../astro-renderer/src/Contracts/Block.php
    :language: php

Pages
-----

Fields
------

Astro includes a number of fields by default. It is not currently possible to create new fields without editing
Astro directly.


Definitions
-----------

Definitions from multiple repositories can be used in the same instance of Astro.

The definitions available to the Editor are defined at build time, whilst those for the

The Editor, API and Renderer all need to be configured with which repositories should be used, and all definitions should be namespaced.

Renderer
........


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



