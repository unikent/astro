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

	public function renewTokens($users = 'all')
	{
		$users = $users == 'all' ? User::all() : User::whereIn('id', array_map('trim', explode(',', $users)))->get();
		
		foreach ($users as $user) {
			$user->generateAPIToken(true);
			$user->save();
		}
	}
}
