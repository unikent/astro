<p align="center">
	<img src="https://github.com/unikent/astro/blob/develop/public/img/logo-full.svg?raw=true" alt="Astro"/>
</p>

## Description
Astro is a web publishing platform built for use by the University of Kent.

Pages are assembled using pre-defined *blocks*. These are arranged into *regions* on page *layouts*.

The system is easy to extend using block, region and layout definitions. These files contain JSON describing the component to the system.

Astro has three separate components:

 - A RESTful JSON **API**, built on Laravel 5.4
 - A JavaScript **editor** interface (a client built with Vue.js)
 - A **renderer**, used to fulfil page requests

## Setup
### Prerequisites

* PHP 5.6.4+
* MySQL 5.5+ (?)
* Node.js and npm/yarn (latest)
* Composer (latest)

### API Installation

1. Checkout the repository
2. Move into the astro path, e.g. `cd /path/to/astro`
3. Install dependencies with `composer install`
4. Copy `.env.example` to `.env` and configure your `DB_` variables.
5. Set your `APP_KEY` variable within `.env`. Running `php artisan key:generate` makes this simple.
6. Pull in your definitions, i.e. for UoK: `git clone git@github.com:unikent/cms-prototype-blocks.git`.
7. Update `DEFINITIONS_PATH` in your .env to point to the newly-cloned definitions directory.
8. Create a symlink so that `storage/app/pubic` can be accessed from `public/storage`
9. Run `php artisan migrate --seed` and `DB_CONNECTION=mysql_test php artisan migrate`
10. Ensure that everything is working properly, by running the test suite: `phpunit`

### Editor Installation
yarn and npm should be interchangeable for these commands

```bash
cd /path/to/astro

// Install project dependencies
yarn install

yarn run dev
```

When developing definitions (blocks, layouts) including vue templates you may find it easier to yarn link to your definitions directory.

Otherwise you will need to run
```bash
yarn update <your-definitions-repo-name>
```
from within your astro directory to update to the latest _committed_ version, before building with yarn.

To do so:
```bash
cd /path/to/DEFINITIONS
yarn link

cd /path/to/astro
yarn link <your-definitions-repo-name>
```

## Developer Notes
### Approach
#### API Requests, Authentication & Authorization
Anyone wishing to access the API will need a registered user with an API Token. An API Token is automatically generated when a User is added to the system.

API requests will need to request a JSON response with an `Accepts: application/json` header, and pass the access token with an `Authorization: Bearer TOKEN` header. Data can be passed to the API as form data or as a JSON object (with a `Content-Type: application/json` header).

The API is intended to be RESTful, uses Laravel naming conventions and should make semantic use of HTTP status codes.

Fractal is used to serialize the API output. This is covered in more depth elsewhere (see: Serialization).


#### Definitions
At present, definition files are read from disk into the application; at a later date this should be refactored to use Redis. Definition files are versioned using their folder hierarchy, although the JSON content also contains a version key.

Definitions are represented within the system as models extending `App\Models\Definitions\BaseDefinition` and implementing `App\Models\Definitions\Contracts\Definition`. Their interface is very similar to Eloquent models but they should be considered immutable objects - as objects they are intended to give definitions a proper object representation within the system.

#### Serialization
Whenever JSON is serialized in the application, [Fractal](http://fractal.thephpleague.com/) is used to transform the data.

Serialization occurs in two main areas: `App\Http\Controllers\Api\v1\*` and when a page is published ($page->publish($transformer) requires a Fractal transformer instance to ensure that the baked JSON is in the correct format for the API to serve directly).

Many of the API endpoints support Fractal 'includes' by passing an '?include=' parameter with an API request. This accepts a comma-separated list of relations to include. Where an endpoint supports includes it is noted in the docblock.

Includes will resolve deep relations, i.e. 'block,block.definitions' but please use sparingly: depth restrictions are permissive but data is often lazy-loaded.

#### Routes, Pages, Sites & Permissions
Pages, Routes and Sites are inter-dependent, at times it is helpful to think of them as a single conceptual unit.

The URL to a Page is stored by its Route, and the Route represents the position of that page within a Site's hierarchy. By associating a Site with a Route, a Page becomes the homepage of a sub-site and enters a new authorization context - a different PublishingGroup governs edit access to it.

All of these objects (Page, Route, Site) are created and updated via a single API endpoint: `/api/v1/page`.

Pages (and their associated Block instances) represent draft content. When a Page instance is published a new PublishedPage instance is created. The `bake` attribute a PublishedPage stores a JSON representation of the Page (its Block instances, and canonical Route) at point of publication.

A single Page may be associated with multiple PublishedPage instances (providing a publication history and audit log). The JSON `bake` stored on a PublishedPage is served as published-content via the `/api/v1/route/resolve?path=` endpoint.


##### Notes on Routes and Redirects
Routes are implemented as a tree hierarchy, using Baum (a nested-set implementation for Laravel).

Normally a Route has a `parent_id` and a `slug`. The `parent_id` associates the Route with its ancestor within the tree. The `slug` provides a URL segment representing the Page. The full path to a Page is automatically generated whenever a Route is saved, by joining the slug of a Route with the slugs of its' ancestors within the tree.

There is a special case where a Route has neither a `slug` nor a `parent_id`. This is considered a "root Route" and is the start of an entirely new Route hierarchy.

A Page can have a maxiumum of two Routes: one active, one draft. When a Page is published, any draft Route is made active (replacing the current active Route). A Redirect is created to ensure that requests using the old URL can be redirected.

Both Routes and Redirects implement the `App\Models\Contracts\Routable` contract, using the `App\Models\Traits\Routable` trait.


##### Notes on Sites
Sites provide a useful authorization context for grouping pages. A site is associated with a PublishingGroup. A users' membership of a PublishingGroup affects their ability to edit a Page within a given Site.

Sites are associated with Routes via `$route->makeSite($site)`. This will update the given Route instance, as well as all other Routes to the associated Page.

To associate a Page with a site, either pass a `site_id` persisting a Page `/api/v1/page` (which will use an existing Site) or pass `site.*` parameters (which will create and associate a new Site model).

When associating a Route with a Site, the change is *immediate*, affects *all* Routes to the given Page, and is not subject to the publication/versioning system.


##### Notes on Publishing
Pages are published via `/api/v1/page/ID/publish`. Internally this calls `$page->publish($transformer)`. When publishing:

 - A new PublishedPage instance is created, with the `bake` attribute populated with JSON (obtained using the Fractal `$transformer`)
 - The latest inactive Route is made both active, and canonical.
 - All other inactive Routes for the given Page are purged.

A Page and all descendants may also be published via `/api/v1/page/ID/publish-tree`.


##### Notes on Deleting
Pages are deleted by a DELETE request to `/api/v1/page/ID/`. This results in a soft-delete, where the Page and its associated Routes still remain in the database.

A DELETE request to `/api/v1/page/ID/force` will result in the Page being deleted entirely from the database. This will cascade at a database level to also delete Routes, Redirects.

PublishedPages will remain in the database (providing a history and allowing potential for a manual recovery process), but without Routes are not routable.


#### Blocks and Pages
Block instances are created when creating or updating a Page (by a POST to `/api/v1/page`, or a PUT to `/api/v1/page/ID`.

It is important to send ALL Block instances to the server when persisting a Page as **all existing block instances are removed** as a part of the persitance proces. Block instances are then re/created based on the submission **matching the order in which they were submitted**.


````
{
	"data": {
		...

		"blocks": {
			"main": [
				{
					"definition_name": "test-block",
					"definition_version": 1
				},

				{
					"definition_name": "test-block",
					"definition_version": 1
				},

				...
			]
		}
	}
}
````

It is possible for Block definitions to contain validation rules, and for Region definitions to list compatible Blocks. This needs to be validated when persisting Block instances. This is currently implemented by the `App\Models\Api\v1\Page\PersistRequest` class using a the `BlockBroker` class:

 - `PersistRequest` defines its own validation rules in the usual way (as a standard FormRequest);
 - the `getRules()` method also loads Block and Region definitions based on the submitted data;
 - a `App\Validation\Brokers\BlockBroker` is then instantiated for each Block instance submitted (this class transforms the validation rules in the block definition to their Laravel-compatible equivalents);
 - the rules are then extracted from the `BlockBroker` and merged into the default ruleset within `PersistRequest`

The `BlockBroker` also supports `getRegionConstraintRules`, where a Region definition is the only parameter. This validates that the `definition_name` on the Block instance is allowed in the given Region.


### Testing
PHPUnit has a good level of code coverage across the entire application.

Controller tests (deliberately) act more as integration tests than unit tests as they test much of the stack by default. Mocks are used for authentication/authorization, which is the main exception to this rule. Policies should be unit tested to ensure that they will function as expected. At present, serializers are not unit tested - their output is assessed by the Controller tests.

Tests are intended to run on their own database connection and will not migrate automatically. With a separate database
connection configured as `mysql_test`, migrations can be run with `DB_CONNECTION=mysql_test php artisan migrate`.

The `mysql_test` connection falls back to use the standard `DB_` config variables by default. They can be overridden using `TEST_DB_` variables. The default connection for tests is set in `phpunit.xml`.
