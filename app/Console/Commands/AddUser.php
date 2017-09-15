<?php

namespace App\Console\Commands;

use App\Models\PublishingGroup;
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
                                {publishinggroup? : The Publishing Group Name (if blank user will be added to a publishing group with their own username).} 
                                {name? : The users name (only required if the user account does not already exist).}
                                {email? : The users email (only required if the user account does not already exist).}
                            ';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Add a user to a publishing group, creating the user if the account does not exist.';

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
        $pubgroup = null;
        $username = $this->argument('username');
        $pubgroup_name = $this->argument('publishinggroup');
        if($pubgroup_name && $pubgroup_name != $username){
            $pubgroup = PublishingGroup::where('name', $pubgroup_name)->first();
            if(!$pubgroup){
                $this->error('Publishing group "' . $pubgroup_name . '" does not exist.');
                return;
            }
        }
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
            }
            if(!$pubgroup) {
                // if we didn't find a pub group above, but got to here, we just want to make sure the
                // user is a member of a publishing group named after their username.
                // New users will have this group, but legacy ones will need it created.
                $pubgroup = PublishingGroup::firstOrCreate(['name' => $username]);
            }
            $pubgroup->users()->syncWithoutDetaching([$user->id]);
            $this->info('User "'.$username.'" added to publishing group "' . ($pubgroup_name ? $pubgroup_name : $username) . '"');
        }else{
            $this->error('Username must be between 1 and 30 alphanumeric characters.');
        }
    }
}
