<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Exception;
use App\Models\Site;
use App\Models\Page;
use App\Models\User;
use App\Models\LocalAPIClient;
use Illuminate\Validation\ValidationException;
use App\Models\Definitions\Region as RegionDefinition;

class UpdateSiteURL extends Command
{
	/**
	 * The name and signature of the console command.
	 *
	 * @var string
	 */
	protected $signature = 'astro:updatesiteurl
								{--site-id=}
								{--new-host=}
								{--new-path=}
								{--url-to-update=}
								{?--yes}
								{?--republish}
								';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = "Update a given site's host, path, and options, including all absolute links in pages";

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
		$new_host = $this->option('new-host'); // TODO: remove http:// or https:// from the front and and trailing '/'
		$new_path = $this->option('new-path'); // TODO ensure there is a begining '/' and remove trailing '/'
		$autoconfirm = $this->option('yes');
		$republish = $this->option('republish');

		// check we have a site
		if (!$site) {
			$this->error("You need to specify the --site-id of the site whose URL you're attempting to update.");
			return;
		}

		// check we have a new host
		if (!$new_host) {
			$this->error("You need to specify the --new-host for the site's URL to be set to.");
			return;
		}

		// check we have a path, or set default
		if (!$new_path) {
			$this->warn("No path specified. We will set an empty path.");
			$new_path = '';
		}

		// Keep old details for later
		$old_host = $site->host;
		$old_path = $site->path;

		// full site URLs
		$this->old_site_url = $this->option('url-to-update') ?: $old_host . $old_path;
		$this->new_site_url = $new_host . $new_path;

		// check that we're not changing to the same URL
		if ($this->old_site_url == $this->new_site_url) {
			$this->warn('Attempting to change site url to its current url. Aborting.');
			return;
		}

		// check that no other site exists with the new URL
		if ($existing_site = Site::where('host', '=', $new_host)->where('path', '=', $new_path)->first()) {
			// if there is, check that we are not deliberately trying to update urls
			if ($this->old_site_url == $old_host . $old_path) {
				$this->error("There is already a site with host '$new_host' and path '$new_path'. Its id is '$existing_site->id'.");
				return;
			}
		}

		// for findind and replacing URLs in json
		$this->old_site_url_escaped = str_replace('/', '\/', $this->old_site_url);
		$this->new_site_url_escaped = str_replace('/', '\/', $this->new_site_url);

		// get user confirmation to proceed
		if (!$autoconfirm) {
			if (!$this->confirm("Changing site URL from '$this->old_site_url' to '$this->new_site_url'. Do you with to continue?")) {
				$this->error('Aborting. Because you said to :-D.');
				return;
			}
		}
		$this->updateSiteURL($site, $new_host, $new_path, $republish);
	}

	public function updateSiteURL($site , $new_host, $new_path, $republish = false)
	{
		// Update site's host and path & site option links
		$site->host = $new_host;
		$site->path = $new_path;

		$new_options = false;
		try {
			$new_options = $this->replaceURLs($site->options);
		} catch (Exception $e) {}

		$site->options =  $new_options ? $new_options : $site->options;

		$site->save();
		$this->info("Updated site '$site->id' host, path and options.");

		// replace any page links in latest revision with host + path prefix
		$pages = $site->draftPages()->get();

		$user = User::where('role', User::ROLE_ADMIN)->first();
		$api = new LocalAPIClient($user);

		foreach ($pages as $page) {
			try {
				$page_url = $site->host . $site->path . $page->generatePath();

				// track whether we have republished the page, because if we have, even if there are no changes to the old draft,
				// we will need to resave it
				$republished = false;

				// if we are republishing, then either:
				// 1) There is no published version, in which case we skip this part and just work on the current draft
				// 2) The published version is the latest draft, in which case we skip this bit, work on the current draft, then republish it
				// 3) The published version isn't the latest draft, in which case we update the published version, republish it, then go on to update the draft too.
				if($republish) {
					$published = $page->publishedVersion();

					// if the published version is not the latest draft...
					if($published && $published->revision_id !== $page->revision_id) {
						$new_published_page_regions = $this->replaceURLs($published->revision->blocks);
						if($new_published_page_regions) {
							$api->updatePageContent($page->id, $new_published_page_regions);
							$api->publishPage($page->id);
							$republished = true;
						}
					}
				}

				// update the draft version of the page if we need to
				$new_page_regions = $this->replaceURLs($page->revision->blocks);
				if ($new_page_regions || $republished) {
					$api->updatePageContent($page->id, $new_page_regions);
					// should we be republishing the latest draft?
					if(!$republished && $published) {
						$api->publishPage($page->id);
					}
					$this->info("Updated page '$page->id' ($page_url)." . ($republished ? ' and republished the previous published version' : ($published ? ' and published it' : '')));
				}
				else {
					$this->warn("Skipping page '$page->id' ($page_url). No urls to update.");
					continue;
				}

			} catch (ValidationException $e) {
				$this->error("Validation error occured whiles attempting to update and / or republish page '$page->id' ($page_url).");
				continue;
			} catch (Exception $e) {
				$this->error("Skipping page '$page->id' ($page_url). Unable to replace URLs: " . $e->getMessage());
				continue;
			}

		}
	}

	/**
	 * This function converts a data array to a json srting and performs a srting
	 * replace on the resulting array
	 * @param array $data
	 * @return array
	 */
	public function replaceURLs($data)
	{
		if (!is_array($data)) {
			throw new Exception("Data for replacing urls must be an array");
		}

		$data = json_encode($data);

		// skip ahead if there is nothing to replace
		if (!strpos($data, $this->old_site_url_escaped)) {
			return false;
		}

		$data = str_replace($this->old_site_url_escaped, $this->new_site_url_escaped, $data);
		$data = json_decode($data, true);

		return $data;
	}
}
