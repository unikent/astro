Astro API Documentation
#######################

The API is intended to be RESTful, uses Laravel naming conventions and should make semantic use of HTTP status codes.

Authentication & Authorization
==============================

Anyone wishing to access the API will need a registered user with an API Token. An API Token is automatically generated
when a User is added to the system and is stored in the users database table in the __api_token__ field.

Requests will use the authorization context of the user whose token is sent.

Required Headers
================

The API Token must be included in the headers of every API Request:

``Authorization: Bearer <TOKEN>``

API requests MUST include the following header to inform the API that the client accepts the JSON that it outputs:

``Accepts: application/json``

API Requests MUST send data as EITHER form-data or as a JSON object. If sending a JSON object the request MUST include
the header

``Content-Type: application/json``

API Requests
============

Sites
~~~~~

List Sites
----------

Details all Sites that the auth token can edit or manage.

``GET /sites[?include=(pages|drafts|published)]``

Response
........

.. literalinclude:: ./examples/api/list-sites-response-simple.json
    :language: javascript
    :caption: Simple response (no "?include="), listing two sites.

* **id** - The site's unique identifier.
* **name** - The human-readable name of the site.
* **options** - Configuration options for the site.
 * **default_layout_name** - The name of the default layout to use when creating pages for this site.
 * **default_layout_version** - The version of the default layout.

Site Details
------------

Get the details for a specific site.

``GET /sites/{id}``

{id} is the id for the site (as returned from the List Sites request).

Site Structure (Pages)
----------------------

``GET /sites/{id}/tree``

Create a new Site
-----------------

``POST /sites``

Update a Site
-------------

``PUT/PATCH /sites/{id}``

Delete a Site
-------------

``DELETE /sites/{id}``

Pages
~~~~~

Add a Page
----------

``POST /pages``

Update Page Content (blocks)
----------------------------

``PUT/PATCH /pages/{id}``

Update Page Metadata
--------------------

Rename a Page's slug
--------------------

Move a Page
-----------

Publish a Page
--------------

``POST /pages/{id}/publish``

Publish a Page and its subpages
-------------------------------

``POST /pages/{id}/publish_tree``

Unpublish a Page and its subpages
---------------------------------

``POST /pages/{id}/unpublish``

Copy a Page
-----------

Copy a Page and its subpages
----------------------------

Delete a Page
-------------

``DELETE /pages/{id}``

Media
~~~~~

Logs
~~~~

