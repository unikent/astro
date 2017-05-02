#Astro
## Description
Astro is a content management platform built for use by the University of Kent. 

Pages can be assembled using pre-defined *blocks*. These can be arranged into *regions* on page *layouts*. 

The system is readily extensible as using block, region and layout definitions. These are files containing a JSON blo a JSON blob describing each component to the system. 

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
5. Pull in your definitions, i.e. for UoK: `git clone git@github.com:unikent/cms-prototype-blocks.git`. 
6. Update `DEFINITIONS_PATH` in your .env to point to the newly-cloned definitions directory.
6. Create a symlink so that `storage/app/pubic` can be accessed from `public/storage`
7. Run `php artisan migrate --seed` and `DB_CONNECTION=mysql_test php artisan migrate`
8. Ensure that everything is working properly, by running the test suite: `phpunit`

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

### Testing
