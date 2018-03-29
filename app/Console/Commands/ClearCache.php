<?php

namespace App\Console\Commands;

use Config;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Redis;

class ClearCache extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'astro:clearcache';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'A tool to crear the redis caches';

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
        // chear the redis cache if its being used
        if (Config::get('database.redis.active')) {
            Redis::flushDB();
        }
    }
}
