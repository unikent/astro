<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Exception;
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

        // Keep old details for later
        $old_host = $site->host;
        $old_path = $site->path;

        // full site URLs
        $this->old_site_url = $old_host . $old_path;
        $this->new_site_url = $new_host . $new_path;

        // for findind and replacing URLs in json
        $this->old_site_url_escaped = str_replace('/', '\/', $this->old_site_url);
        $this->new_site_url_escaped = str_replace('/', '\/', $this->new_site_url);

        $this->updateSiteURL($site, $new_host, $new_path);
    }

    public function updateSiteURL($site , $new_host, $new_path)
    {

        // Update site's host and path & site option links
        $site->host = $new_host;
        $site->path = $new_path;
        $site->options = $this->replaceURLs($site->options);

        $site->save();
        $this->info("Updated site '$site->id' from '$this->old_site_url' to '$this->new_site_url', including site options");

        // replace any page links in latest revision with host + path prefix
        $pages = $site->draftPages()->get();

        $user = User::where('role', User::ROLE_ADMIN)->first();
        $api = new LocalAPIClient($user);

        foreach ($pages as $page) {
            $page_url = $site->host . $site->path . $page->generatePath();
            $page_regions = json_encode($page->revision->blocks);

            // skip ahead if there is nothing to replace
            if (!strpos($page_regions, $this->old_site_url_escaped)) {
                $this->warn("Skipping page '$page->id' ($page_url). No urls to update. Old site url:" . $this->old_site_url);
                continue;
            }

            $new_page_regions = $this->replaceURLs($page_regions);

            try {

                $api->updatePageContent($page->id, $new_page_regions);
                $this->info("Updated page '$page->id' ($page_url), replaced old urls '$old_site_url' with new urls '$new_site_url'");
            } catch (ValidationException $e) {
                $this->error("Validation error occured whiles attempting to update '$page->id'");
            } catch (Exception $e) {
                $this->error("Error occured whiles attempting to update '$page->id' ($page_url)");
            }
        }
    }

    /**
     *
     *
     */
    public function replaceURLs($data)
    {
        if (!is_array($data)) {
            // dd($data);
            throw new Exception("Data must be an array");
        }

        $data = json_encode($data);
        $data = str_replace($this->old_site_url_escaped, $this->new_site_url_escaped, $data);
        $data = json_decode($data, true);

        return $data;
    }
}
