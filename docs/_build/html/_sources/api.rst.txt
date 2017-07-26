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

Site Details
------------

Site Structure (Pages)
----------------------

Create a new Site
-----------------

Update a Site
-------------

Delete a Site
-------------

Pages
~~~~~

Add a Page
----------

Update Page Content (blocks)
----------------------------

Update Page Metadata
--------------------

Rename a Page's slug
--------------------

Move a Page
-----------

Publish a Page
--------------

Publish a Page and its subpages
-------------------------------

Unpublish a Page and its subpages
---------------------------------

Copy a Page
-----------

Copy a Page and its subpages
----------------------------

Delete a Page
-------------

Logs
~~~~

