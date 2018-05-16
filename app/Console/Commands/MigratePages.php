<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

use App\Models\Site;
use App\Models\Page;
use App\Models\User;
use App\Models\LocalAPIClient;
use Illuminate\Validation\ValidationException;
use App\Models\Definitions\Region as RegionDefinition;

class MigratePages extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'astro:migratepages
                                {action : The action to perform. Currently only supports "add-region"}
                                {--id=}
                                {--site-ids=}
                                ';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Migrates pages where a region has been added';

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
        switch ($this->argument('action')) {
            case 'add-region':
                if ($this->option('id')) {
                    $this->addRegion($this->option('id'), $this->option('site-ids') ? $this->option('site-ids') : 'all');
                }
                else {
                    $this->error("You need to specify the --id of the region you're attempting to add");
                }
                break;
            default:
                $this->error('Invalid action specified.');
                break;
        }
    }

    public function addRegion($region_id , $sites='all')
    {
        $sites = $sites == 'all' ? Site::all() : Site::whereIn('id', array_map('trim', explode(',', $sites)))->get();

        $user = User::where('role', 'admin')->first();
        $api = new LocalAPIClient($user);

        foreach ($sites as $site) {
            $pages = $site->draftPages()->get();

            foreach ($pages as $page) {
                $page->getLayoutDefinition(true); // 'true' to include region definitions
                $region_definition = false;

                foreach ($page->layoutDefinition->getRegionDefinitions() as $rd) {
                    if (RegionDefinition::idFromNameAndVersion($rd->name, $rd->version) === $region_id) {
                        $region_definition = $rd;
                        break;
                    }
                }
                $page_url = $site->host . $site->path . $page->generatePath();
                if (!$region_definition) {
                    $this->warn("Skipping page '$page->id' ($page_url). The '$region_id' region isnt in the definition of this page");
                    continue;
                }

                $page_regions = $page->revision->blocks;
                if (isset($page_regions[RegionDefinition::idFromNameAndVersion($region_definition->name, $region_definition->version)])) {
                    $this->warn("Skipping page '$page->id' ($page_url). Found region '$region_id'");
                    continue;
                }
                try {
                    $page_regions[RegionDefinition::idFromNameAndVersion($region_definition->name, $region_definition->version)] = $region_definition->getDefaultBlocks();
                    $api->updatePageContent($page->id, $page_regions);
                    $this->info("Updated page '$page->id' ($page_url) with a default '$region_id' region");
                } catch (ValidationException $e) {
                    $this->error("Validation error occured whiles attempting to update '$page->id' ($page_url) with a default '$region_id' region");
                } catch (Exception $e) {
                    $this->error("Error occured whiles attempting to update '$page->id' ($page_url) with a default '$region_id' region");
                }
            }
        }
    }
}
