<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;

class AddUser extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'astro:adduser 
                                {username} 
                                {publishinggroup : The Publishing Group Name} 
                            ';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Add a user to a publishing group.';

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
        if(preg_match('/^[a-z0-9_-]{1,30}$/i', $username)){
            $user = User::where('username', $username)->first();
            if(!$user){
                if(!preg_match('/^[a-z0-9\' ]$/i', $name)){
                    $name = $username;
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
                $user = User::create([
                    'username' => $username,
                    'password' => Hash::make($password),
                    'role' => 'user',
                    'name' => $name
                ]);
                $this->info('User created.');
            }else {
                $this->error('User "' . $username . '" already exists.');
            }
        }else{
            $this->error('Username must be between 1 and 30 alphanumeric characters.');
        }
    }
}
