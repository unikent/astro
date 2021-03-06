<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Site;
use App\Models\Page;
use App\Models\User;
use App\Models\UserSiteRole;
use App\Models\LocalAPIClient;

class CopySite extends Command
{
	/**
	 * The name and signature of the console command.
	 *
	 * @var string
	 */
	protected $signature = 'astro:copysite
								{--site-id=}
								{--new-name=}
								{--new-host=}
								{--new-path=}
								';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Copy a site, along with its options, pages, menu, media items, users, roles and permissions ';

	/**
	 * Create a new command instance.
	 *
	 * @return void
	 */
	public function __construct()
	{
		parent::__construct();
	}

	/**
	 * Execute the console command.
	 *
	 * @return mixed
	 */
	public function handle()
	{
		$site = Site::find(intval($this->option('site-id')));

		// check we have a site
		if (!$site) {
			$this->error("You need to specify the --site-id of the site you are attempting to copy.");
			return;
		}

		$new_name = $this->option('new-name') ?: $site->name . ' - ' . date("Y-m-d-His");
		$new_host = $this->option('new-host') ?: $site->host; // TODO: remove http:// or https:// from the front and and trailing '/'
		$new_path = $this->option('new-path') ?: $site->path; // TODO ensure there is a begining '/' and remove trailing '/'

		//ensure we are not using the same host/path combination
		if ($new_host . $new_path == $site->host . $site->path) {
			$new_path = $new_path . '-' . date("Y-m-d-His");
		}
		
		$this->info('Copying site');

		$user = User::where('role', User::ROLE_ADMIN)->first();
		$api = new LocalAPIClient($user);
		$new_site = null;

		$new_site = $api->createSite(
			$new_name, 
			$new_host, 
			$new_path, 
			[
				'name' => $site->site_definition_name, 
				'version' => $site->site_definition_version
			], 
			$options = $site->options,
			false // dont create the default pages
		);
		$this->info("Site copied. New site id: {$new_site->id}.");

		$this->info("Copying pages...");
		$new_pages = [];
		foreach ($site->draftPages()->get() as $page) {
			$new_page = null;
			// get the new page
			if (is_null($page->parent_id)) {
				$new_page = $new_site->draftHomepage()->first();
			} else {
				// create the page if its not a home page
				$new_page = $api->addPage(
					$new_pages[$page->parent_id]->id, 
					null, // leave next_id blank
					$page->slug, 
					[
						'name' => $page->revision->layout_name,
						'version' => $page->revision->layout_version
					],
					$page->revision->title
				);
			}
			$new_pages[$page->id] = $new_page;

			$published_version = $page->publishedVersion();

			//Where there is a published version, update the page with the published revision and publish it
			if ($published_version) {
				$api->updatePageContent($new_page->id, $published_version->revision->blocks);
				$api->publishPage($new_page->id);
			}

			// where there isnt a published version or the draft version is not the same as the published version, update the page with the draft version
			if (!$published_version || $page->revision->id != $published_version->revision->id) {
				$api->updatePageContent($new_page->id, $page->revision->blocks);
			}

			$this->info("Page '{$new_page->revision->title}' added, id: {$new_page->id}.");
		}

		// run update site url to update urls in site options and pages
		$this->info("Updating site urls...");
		$this->call('astro:updatesiteurl', [
			'--site-id' => $new_site->id, 
			'--new-host' => $new_site->host,
			'--new-path' => $new_site->path,
			'--url-to-update' => $site->host . $site->path,
			'--yes' => true
		]);


		// copy over media items
		$this->info("Associating media items...");
		foreach ($site->media as $media_item) {
			$new_site->media()->save($media_item);
			$this->info("Media item '{$media_item->filename}' has been added to the media library");
		}

		// copy over users and roles
		$this->info("Associating users...");
		foreach ($site->usersRoles as $userRole) {
			UserSiteRole::create([
				'user_id' => $userRole->user->id,
				'site_id' => $new_site->id,
				'role_id' => $userRole->role->id
			]);
			$this->info("User '{$userRole->user->name}' added as {$userRole->role->name}");

		}
	}
}
