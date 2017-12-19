<?php

namespace Astro\API\Console\Commands;

use Astro\API\Models\LocalAPIClient;
use Illuminate\Console\Command;
use Illuminate\Validation\ValidationException;
use App\Models\User;

class AddSite extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'astro:addsite
                                {name : The name for the site.}
                                {host : The host or domain name for the site.}
                                {path : The root path for the URL for the site.}
                                {layout : The layout to use for the homepage of the site in the form NAME-VERSION.}
                                ';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Creates a new site.';

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
        $host = $this->argument('host');
        $path = $this->argument('path');
        $layout = $this->argument('layout');
        if(!preg_match('/^([a-z0-9_-]+)-v([0-9]+)$/i', $layout, $matches)){
            $this->error('Layout name and version must be provided in the format {layout-name}-v{layout_version}.');
            return;
        }
        $user = User::where('role', 'admin')->first();
        $api = new LocalAPIClient($user);
        try{
            $site = $api->createSite($name, $host, $path, [ 'name' => $matches[1], 'version' => $matches[2] ]);
            $this->info('Site "'.$site->name.'" created.');
        }catch( ValidationException $e){
            foreach($e->validator->getMessageBag()->toArray() as $field => $errors){
                $err = $field . "\n";
                foreach($errors as $e){
                    $err .= ' * ' . $e . "\n";
                }
                $this->error($err);
            }
        }
    }
}
