<?php

use Astro\API\Models\Revision;
use Astro\API\Models\Block;
use Illuminate\Database\Migrations\Migration;

/**
 * Migrates any block definitions not using the {name}-v{version} format for identifying regions.
 */
class MigrateDataToIncludeVersionsInDefinitions extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
	{
		$count = $updated = 0;
		foreach (Revision::all() as $revision) {
			$json = $revision->blocks;
			$count++;
			if (!empty($json)) {
				$data = [];
				$changed = false;
				foreach ($json as $name => $sections) {
					if (!preg_match('/-v[0-9]+$/', $name)) {
						$name = $name . '-v1';
						$changed = true;
					}
					$data[$name] = $sections;
				}
				if($changed) {
					$revision->blocks = $data;
					$revision->save();
					$updated++;
				}
			}
		}
		echo "$updated of $count revisions updated to use {name}-v{version} syntax for identifying regions.\n";
		$updated = $count = 0;
		foreach (Block::all() as $block) {
			$count++;
			if (!preg_match('/-v[0-9]+$/', $block->region_name)) {
				$updated++;
				$block->region_name .= '-v1';
				$block->save();
			}
		}
		echo "$updated of $count blocks updated to use {name}-v{version} syntax for identifying regions.\n";
	}

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
