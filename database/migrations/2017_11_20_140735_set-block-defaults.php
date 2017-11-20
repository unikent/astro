<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use App\Models\Revision;

class SetBlockDefaults extends Migration
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
			if(empty($json)){
				
				$json = [
					'main' => [
						[
							'name' => 'catch-all',
							'blocks' => []
						]
					]
				];

				$revision->blocks = $json;
				$revision->save();
				$updated++;
			}
		}
		echo "Default blocks have been set for $updated of $count revisions.\n";
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
