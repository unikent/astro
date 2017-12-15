<p align="center">
	<img src="https://cdn.rawgit.com/unikent/astro/develop/public/img/logo-full.svg" alt="Astro" width="85%" height="auto" />
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
8. Create a symlink in the `public` folder to link `uploads` to `storage/app/public/uploads`. Depending on your system this would be with something like `ln -s ../storage/app/public/uploads/ uploads`.
9. Run `php artisan migrate --seed` and `DB_CONNECTION=mysql_test php artisan migrate`
10. Run `php artisan astro:permissions refresh` to setup roles and permissions
11. Ensure that everything is working properly, by running the test suite: `phpunit`

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

Serialization occurs in two main areas: `Astro\API\Http\Controllers\Api\v1\*` and when a page is published ($page->publish($transformer) requires a Fractal transformer instance to ensure that the baked JSON is in the correct format for the API to serve directly).

Many of the API endpoints support Fractal 'includes' by passing an '?include=' parameter with an API request. This accepts a comma-separated list of relations to include. Where an endpoint supports includes it is noted in the docblock.

Includes will resolve deep relations, i.e. 'block,block.definitions' but please use sparingly: depth restrictions are permissive but data is often lazy-loaded.

#### Pages, Revisions, Sites & Permissions

A **Site** has a host (domain) name, an optional path prefix, and one or more hierarchical structures (versions - 
e.g. "draft", "published"), each of which has at least one **Page** (homepage). 

A **Site** structure is comprised of **Pages**, stored as a nested set (using Baum, a nested-set implementation for Laravel)
, scoped to that **Site** and **Version**.

A **Page** has a **Revision** which represents its current content (blocks), title and configuration.

A new **Revision** is created every time page content or definition is updated (but not for a Page move).
**Revisions** have a created date, modified date, published date and deleted date and are associated with a single Page.
 
The URL to a **Page** is represented by concatenating its **Site's** hostname and path and the **Page's** path. 
The **Page's** path is generated automatically when saved by appending its slug to the end of its parent's path.

A single **Page** may be associated with multiple **Revisions** (providing a publication history and audit log)

When a **Page** is moved, a **Redirect** is created to point the old path to the new. If a new **Page** is created at
the old location, the redirection is removed.

All changes to **Pages** including edits, moves, deletions and additions are made in the **draft** site structure, 
and must be published to the **live** site structure in order for them to take effect on the live version of the site.

When a **Page** is published a new **Page** is created with the same site_id, path, slug, revision_id and a version of "published",
putting it in a completely separate nested set **scope** (tree) to the draft version.

##### Authorization 

Only admin users can create and manage **Sites**.

Users are added to **Roles** on **Sites**. Each **Role** has a predefined list of **Permissions** which determine what a user
can do on the site they have a role on.

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

It is important to send ALL Block instances to the server when persisting a Page as **all existing block instances are removed** as a part of the persitance proces. 
Block instances are then re/created based on the submission **matching the order in which they were submitted**.


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

It is possible for Block definitions to contain validation rules, and for Region definitions to list compatible Blocks. 
This needs to be validated when persisting Block instances. 
This is currently implemented by the `App\Models\Api\v1\Page\PersistRequest` class using a the `BlockBroker` class:

 - `PersistRequest` defines its own validation rules in the usual way (as a standard FormRequest);
 - the `getRules()` method also loads Block and Region definitions based on the submitted data;
 - a `App\Validation\Brokers\BlockBroker` is then instantiated for each Block instance submitted (this class transforms the validation rules in the block definition to their Laravel-compatible equivalents);
 - the rules are then extracted from the `BlockBroker` and merged into the default ruleset within `PersistRequest`

The `BlockBroker` also supports `getRegionConstraintRules`, where a Region definition is the only parameter. This validates that the `definition_name` on the Block instance is allowed in the given Region.


### Notes on setting up Roles and Permissions
The roles and permissions can be set up or refreshed with the command `php artisan astro:permissions refresh`. This will add the roles and permissions specified in the `public static $roles_and_permissions` array, which can be found in the 'app/Console/Commands/SetupPermissions.php' class. It will also remove any roles/permissions that have been romoved or even renamed (adding the renamed version as a new role).

To simply rename a role, use `php artisan astro:permissions rename-role --old-name="<name of role to be renamed>" --new-name="<new name of role to be renamed>"`.


### Testing
PHPUnit has a good level of code coverage across the entire application.

Controller tests (deliberately) act more as integration tests than unit tests as they test much of the stack by default. Mocks are used for authentication/authorization, which is the main exception to this rule. Policies should be unit tested to ensure that they will function as expected. At present, serializers are not unit tested - their output is assessed by the Controller tests.

Tests are intended to run on their own database connection and will not migrate automatically. With a separate database
connection configured as `mysql_test`, migrations can be run with `DB_CONNECTION=mysql_test php artisan migrate`.

The `mysql_test` connection falls back to use the standard `DB_` config variables by default. They can be overridden using `TEST_DB_` variables. The default connection for tests is set in `phpunit.xml`.

## Acknowledgements

- [Laravel](https://github.com/laravel/framework) ([MIT](http://opensource.org/licenses/MIT))
- [Vue](https://github.com/vuejs/vue) ([MIT](http://opensource.org/licenses/MIT))
  
Thanks for making our lives easier!

## License

Astro is licensed under the [MIT license](LICENSE).
