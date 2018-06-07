<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

use App\Models\User;

class RenewUserAPITokens extends Command
{
	/**
	 * The name and signature of the console command.
	 *
	 * @var string
	 */
	protected $signature = 'astro:renewapitokens
								{--user-ids=}
								';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = "Renews the user's API tokens";

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
		$this->renewTokens($this->option('user-ids') ? $this->option('user-ids') : 'all');
	}

	/**
	 * Generate new tokens for the given users.
	 * @param string $users A comma seperated list of user IDs' to generate new api tokents for. If 
	 * user='all', leave out the astro-www user, since it is needed for the front end to funtion.
	 * To renew astro-www's token, provide its ID explicitly.
	 * @return mixed
	 */
	public function renewTokens($users = 'all')
	{
		$users = $users == 'all' ? User::where('username', '!=', 'astro-www')->get() : User::whereIn('id', array_map('trim', explode(',', $users)))->get();
		
		foreach ($users as $user) {
			$user->generateAPIToken(true);
			$user->save();
			$this->info("Renewed API token for user: '$user->name' ");
		}

		if(count($users) === 0){
			$this->warn("No matching users found");
		}
	}
}
