<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Site;

class SetSiteOption extends Command
{
	/**
	 * The name and signature of the console command.
	 *
	 * @var string
	 */
	protected $signature = 'astro:setsiteoption
								{--site-id=}
								{--key=}
								{--value=}
								';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = "Set or update a site option";

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
		$key = $this->option('key');
		$value = $this->option('value');

		// check we have a site
		if (!$site) {
			$this->error("Site not found. Please specify the --site-id of the site whose options you're attempting to update.");
			return;
		}

		// check we have a key
		if (!$key) {
			$this->error("You need to specify the --key of the site option to be set.");
			return;
		}

		// check we have a value, or set default
		if (empty($value)) {
			$this->warn("No value specified.");
			$value = null;
		}

		// Get confirmation message
		$old_value = !empty($site->options[$key]) ? $site->options[$key] : false;
		$message = '';
		if(is_null($value)){
			$message = "Unsetting '$site->name' site's '$key' option. Do you with to continue?";
		}
		elseif (!empty($old_value)) {
			$message = "Changing '$site->name' site's '$key' option from '$old_value' to '$value'. Do you with to continue?";
		}
		else{
			$message = "Setting '$site->name' site's '$key' option to '$value'. Do you with to continue?";
		}

		// get user confirmation to proceed
		if (!$this->confirm($message)) {
			$this->info('Aborting. Because you said to :-D.');
			return;
		}

		$site->setOption($key, $value);
	}
}
