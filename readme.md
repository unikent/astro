#Astro
## Description
Astro is a content management platform built for use by the University of Kent. 

Pages can be assembled using pre-defined *blocks*. These can be arranged into *regions* on page *layouts*. 

The system is readily extensible as using block, region and layout definitions. These are files containing a JSON blob which describes the component to the system. 

Astro has three separate components:
 
 - A RESTful JSON **API**, built on Laravel 5.4
 - A Javascript **editor** interface (a client built with Vue.js)
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

// Get the project dependencies
composer install
yarn install
```

## Developer Notes
### Approach
#### Routes, Pages, Sites & Permissions
Routes give hierarchy to pages within Astro and are implemented as a nested-set using Baum.

A route which has neither a `slug` or a `parent_id` is considered a "root Route". All other Routes should have an ancestor within the tree and have a `slug` specified. By joining the slugs of a Route and its ancestors, a path can be generated. This is done automatically when saving a Route.

Every Route should be associated with a Page and may optionally be associated with a Site. When a Route is associated with a Site it, along with its descendants, are treated as a separate authorization context - each Site is associated with one PublishingGroup. 

When a Route is associated with a Site, its Page is the homepage of a subsite.

It is possible for one Page to have multiple Routes (and thus multiple positions within the site hierarchy). The primary route to a page should always have the `is_canonical` flag set to true. By calling `$route->makeCanonical()` the given Route gains canonical status and all other Route instances associated with the same Page lose it. 

If a non-canoncical Route is ever involved in a path collision (i.e. by another Page at the same position in the tree), it will get re-associated.

Routes and Sites are created automatically when creating a Page with a POST to `/api/v1/page`.


#### Definitions
At present, definition files are read from disk into the application; at a later date this should be refactored to use Redis. Definition files are versioned using their folder hierarchy, although the JSON content also contains a version key.

Definitions are represented within the system as models extending `App\Models\Definitions\BaseDefinition` and implementing `App\Models\Definitions\Contracts\Definition`. Their interface is very similar to Eloquent models but they should be considered immutable objects - as objects they are intended to give definitions a proper object representation within the system. 

### Testing
PHPUnit has a good level of code coverage across the entire application. 

Controller tests (deliberately) act more as integration tests than unit tests as they test much of the stack by default. Mocks are used for authentication/authorization, which is the main exception to this rule. Policies should be unit tested to ensure that they will function as expected. At present, serializers are not unit tested - their output is assessed by the Controller tests.

Tests are intended to run on their own database connection and will not migrate automatically. With a separate database
connection configured as `mysql_test`, migrations can be run with `DB_CONNECTION=mysql_test php artisan migrate`.

The `mysql_test` connection falls back to use the standard `DB_` config variables by default. They can be overridden using `TEST_DB_` variables. The default connection for tests is set in `phpunit.xml`.