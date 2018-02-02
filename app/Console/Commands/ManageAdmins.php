<?php

namespace App\Console\Commands;

use ArrayObject;
use Illuminate\Console\Command;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class ManageAdmins extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'astro:admins
    							{action? : The action to perform ("list", "+" or "-"). If blank, defaults to "list".}
    							{username? : The username of the user to add / remove from the list of admins. If blank or no match all users beginning with prefix will be listed.}
                            ';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Manage superadmin users.';

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
        $action = $this->argument('action');
        $username = $this->argument('username');
        if(!in_array($action, ['+', '-'])){
        	$action = 'list';
		}
		switch( $action ){
			case '+':
			case '-':
				$user = User::query()->where('username', $username)->first();
				if (!$user) {
					$this->error('User "' . $username . '" does not exist.');
					$users = User::query()->where('username', 'like', $username . '%')->get();
					if($users->count()) {
						$this->warn('Did you mean?');
						foreach ($users as $item) {
							$this->info("\t" . $item->username . ($item->role == 'admin' ? ' ADMIN' : ''));
						}
					}
				}
				if($user) {
					$user->role = ('+' == $action ? 'admin' : 'user');
					$user->save();
					$this->info('User "' . $username . '" is ' . ('+' == $action ? '' : 'no longer ') . 'an admin.');
				}
				break;
			default:
				$admins = User::query()->where('role', '=', 'admin')->orderBy('username')->get();
				$this->alert('There are ' . $admins->count() . ' superadmins.');
				foreach($admins as $admin){
					$this->info($admin->username . ' (' . $admin->email . ')');
				}
				break;
		}
    }
}
