<?php
namespace Astro\API\Tests;

use File as FileSystem;

trait FileCleanupTrait
{

    public function tearDown(){
        $files = FileSystem::glob(sys_get_temp_dir() . '/astro_test*');
        FileSystem::delete($files);

        $files = FileSystem::deleteDirectory(storage_path('tests'));
        FileSystem::delete($files);

        return parent::tearDown();
    }

}
