<?php

namespace Astro\API\Console\Commands;

use ArrayObject;
use Illuminate\Console\Command;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AddUser extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'astro:adduser 
                                {username}
                                {name? : The users name (only required if the user account does not already exist).}
                                {email? : The users email (only required if the user account does not already exist).}
                            ';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Add a user if the account does not exist.';

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
        $name = $this->argument('name');
        $username = $this->argument('username');
        $email = $this->argument('email');
        if(preg_match('/^[a-z0-9_-]{1,30}$/i', $username)){
            $user = User::where('username', $username)->first();
            if(!$user){
                if(!preg_match('/^[a-z0-9\' ]$/i', $name)){
                    $name = $username;
                }
                while(!filter_var($email, FILTER_VALIDATE_EMAIL)){
                    $email = $this->ask('Please enter the email address for this user account: ');
                }
                $password = '';
                while(!$password) {
                    $password = $this->secret('Password');
                    $password2 = $this->secret('Repeat Password');
                    if($password != $password2){
                        $password = '';
                        $this->error('Passwords do not match. Please try again.');
                    }
                }
                $user = new User();
                $user->username = $username;
                $user->password = Hash::make($password);
                $user->role = 'user';
                $user->name = $name;
                $user->email = $email;
                $user->settings =new ArrayObject();
                $user->save();
                $this->info('User created.');
				$this->info('User "'.$username.'" added.');
            }else{
            	$this->info('User "'.$username.'" already exists.');
			}
        }else{
            $this->error('Username must be between 1 and 30 alphanumeric characters.');
        }
    }
}
