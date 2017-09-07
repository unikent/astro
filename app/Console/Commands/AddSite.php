<?php

namespace App\Console\Commands;

use App\Models\LocalAPIClient;
use App\Models\PublishingGroup;
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
                                {publishinggroup : The name of the publishing group for the site.}
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
    protected $description = 'Creates a new site, assigning it to an existing publishing group.';

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
        $pg_name = $this->argument('publishinggroup');
        if(!preg_match('/^[a-z0-9_ -]+$/i', $pg_name)){
            $this->error('"' . $pg_name . '" is not a valid publishing group name.');
            return;
        }
        $pubgroup = PublishingGroup::where('name', $pg_name)->first();
        if(!$pubgroup){
            $pubgroup = PublishingGroup::create([
               'name' => $pg_name
            ]);
            $this->info('Publishing group "'.$pg_name.'" created.');
        }
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
            $site = $api->createSite($pubgroup->id, $name, $host, $path, [ 'name' => $matches[1], 'version' => $matches[2] ]);
            $this->info('Site "'.$site->name.'" created in publishing group "'.$site->publishing_group->name . '"');
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
