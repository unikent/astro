#Astro

## Prerequisites

* PHP 5.6.4+
* MySQL 5.5+ (?)
* Node.js and npm/yarn (latest)
* Composer (latest)
* Studio (`composer global require franzl/studio`)

## Setup

## Commands to run
yarn and npm should be interchangeable for these commands

```bash
cd /path/to/astro

// Get the project dependencies
composer install
yarn install

// create symlink from storage folder to public folder
ln -s /path/to/astro/storage/app/public /path/to/astro/public/storage

// or for windows
mklink /d /j C:\path\to\astro\public\storage C:\path\to\astro\storage\app\public
```

### Developing blocks/themes

```bash
cd /path/to/theme-blocks && yarn link
cd /path/to/astro && yarn link theme-blocks-repo-name

```

> TODO: finish documenting