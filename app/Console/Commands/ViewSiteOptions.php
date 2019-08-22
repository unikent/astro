<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Site;

class ViewSiteOptions extends Command
{
	/**
	 * The name and signature of the console command.
	 *
	 * @var string
	 */
	protected $signature = 'astro:viewsiteoptions
								{--site-id=}
								';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = "View a site's options";

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
			$this->error("Site not found. Please specify the --site-id of the site whose options you want to view.");
			return;
		}

		$this->info(print_r($site->options, true));
	}
}
