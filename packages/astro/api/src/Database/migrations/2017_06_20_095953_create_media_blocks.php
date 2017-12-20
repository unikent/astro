<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMediaBlocks extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('media_blocks', function(Blueprint $table) {
			$table->increments('id');

			$table->integer('media_id')->unsigned()->index();
			$table->integer('block_id')->unsigned()->index();

			$table->string('block_associated_field');

			$table->foreign('media_id', 'media_blocks_media_id_fk')
				->references('id')->on('media')->onDelete('cascade');

			$table->foreign('block_id', 'media_blocks_block_id_fk')
				->references('id')->on('blocks')->onDelete('cascade');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('media_blocks', function(Blueprint $table) {
			$table->dropForeign('media_blocks_media_id_fk');
			$table->dropForeign('media_blocks_block_id_fk');
		});

		Schema::drop('media_blocks');
	}
}
