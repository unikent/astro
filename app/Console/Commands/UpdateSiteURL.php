<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

use App\Models\Site;
use App\Models\Page;
use App\Models\User;
use App\Models\LocalAPIClient;
use Illuminate\Validation\ValidationException;
use App\Models\Definitions\Region as RegionDefinition;

class UpdateSiteURL extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'astro:updatesiteurl
                                {--site-id=}
                                {--new-host=}
                                {--new-path=}
                                ';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update a given site\'s host and path, includin all absolute links in pages';

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
        $new_host = $this->option('new-host'); // TODO: remove http:// or https:// from the front and and trailing '/'
        $new_path = $this->option('new-path'); // TODO ensure there is a begining '/' and remove trailing '/'

        if (!$site) {
            $this->error("You need to specify the --site-id of the site whose URL you're attempting to update");
            return;
        }

        if (!$new_host) {
            $this->error("You need to specify the --new-host for the site's URL to be set to");
            return;
        }

        if (!$new_path) {
            $this->info("No path specified. Will use an empty path.");
            $new_path = '';
        }

        $this->updateSiteURL($site, $new_host, $new_path);
    }

    public function updateSiteURL($site , $new_host, $new_path)
    {
        // keep old details for later
        $old_host = $site->host;
        $old_path = $site->path;

        //Update site's host and path
        $site->host = $new_host;
        $site->path = $new_path;
        $site->save();

        // replace any internal links in latest revision with host + path prefix
        $old_site_url = $old_host . $old_path;
        $new_site_url = $new_host . $new_path;
        $pages = $site->draftPages()->get();

        $user = User::where('role', User::ROLE_ADMIN)->first();
        $api = new LocalAPIClient($user);

        foreach ($pages as $page) {
            $page_url = $site->host . $site->path . $page->generatePath();
            $page_regions = json_encode($page->revision->blocks);

            // skip ahead if there is nothing to replace
            if (strpos($page_regions, str_replace('/', '\/', $old_site_url)) === false) {
                $this->warn("Skipping page '$page->id' ($page_url). No urls to update. Old site url:" . str_replace('/', '\/', $old_site_url));
                continue;
            }

            $new_page_regions = str_replace(str_replace('/', '\/', $old_site_url), str_replace('/', '\/', $new_site_url), $page_regions);

            try {

                $api->updatePageContent($page->id, $new_page_regions);
                $this->info("Updated page '$page->id' ($page_url), replaced old urls '$old_site_url' with new urls '$new_site_url'");
            } catch (ValidationException $e) {
                $this->error("Validation error occured whiles attempting to update '$page->id'");
            } catch (Exception $e) {
                $this->error("Error occured whiles attempting to update '$page->id' ($page_url)");
            }
        }

        // replace and links in site options
    }
}
