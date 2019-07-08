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
		$site = Site::find(intval($this->option('site-id')));
		$new_name = $this->option('new-name');
		$new_host = $this->option('new-host'); // TODO: remove http:// or https:// from the front and and trailing '/'
		$new_path = $this->option('new-path'); // TODO ensure there is a begining '/' and remove trailing '/'

		// check we have a site
		if (!$site) {
			$this->error("You need to specify the --site-id of the site whose URL you're attempting to update.");
			return;
		}

		// // check we have a name
		// if (!$new_name) {
		// 	$new_name = $site->name;
		// }

		$new_site = $site->replicate();
		$new_site->name = $new_name ?: $new_site->name . ' - ' . date("Ymd");
		$new_site->host = $new_host ?: $new_site->host;
		$new_site->path = $new_path ?: $new_site->path;

		//ensure we are not using the same host/path combination
		if ($new_site->host . $new_site->path == $site->host . $site->path) {
			$new_site->path = $new_site->path . '-' . date("Ymd");
		}

		$new_site->save();

		dd($new_site);
	}
}
