<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Site;
use App\Models\Page;
use App\Models\User;
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
		$this->info('starting');
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
		
		$this->info('copying site');

		$user = User::where('role', User::ROLE_ADMIN)->first();
		$api = new LocalAPIClient($user);

		try {
			$api->createSite(
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
			$this->info('site copied');
		} catch (ValidationException $e) {
			$this->error("Validation error occured whiles attempting to copy the site.");
			return;
		} catch (Exception $e) {
			$this->error("Error occured whiles attempting to copy the site.");
			return;
		}


		// // copy pages over to new site
		// $pages = $site->draftPages()->get();
		// $pages = $pages->merge($site->publishedPages()->get());

		// foreach ($pages as $page) {
		// 	$this->info("copying {$page->version} page {$page->id}");
		// 	$new_page = $page->replicate();
		// 	$new_page->site_id = $new_site->id;

		// 	$old_parent = $pages->where('id', '=', $page->parent_id)->first();
		// 	if ($old_parent) {
		// 		$new_parent = $new_site->pages->where('path', '=', $parent->path)->andWhere('version', '=', $parent->version)->firstOrFail();
		// 		$new_page->makeChildOf($new_parent);
		// 	}

		// 	//duplicate page's revisions
		// 	$new_revision = $page->revision->replicate();
		// 	$new_revision->save();


		// 	$new_page->setRevision($new_revision);

		// 	$new_page->save();
		// 	$this->info("new {$new_page->version} page {$new_page->id} copied with revision {$new_revision->id}");
		// }

		// run update site url to update site options and pages

	}
}
