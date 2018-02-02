<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use App\Models\Revision;

class AddSectionsToRevisionBlocks extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
    	$count = $updated = 0;
		foreach(Revision::all() as $revision){
			$json = $revision->blocks;
			$count++;
			if($json){
				if(isset($json['main'])
					&& is_array($json['main'])
					&& count($json['main']) > 0
					&& isset($json['main'][0]['definition_name'])) {
					$section = ['name' => 'catch-all', 'blocks' => $json['main']];
					$json['main'] = [$section];
					$revision->blocks = $json;
					$revision->save();
					$updated++;
				}
			}
		}
		echo "Updated $updated of $count revisions.\n";
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
    }
}
