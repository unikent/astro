<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMediaPublishingGroups extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('media_publishing_groups', function(Blueprint $table) {
			$table->increments('id');

            $table->integer('media_id')->unsigned()->index();
            $table->integer('publishing_group_id')->unsigned()->index();

            $table->unique([ 'media_id', 'publishing_group_id' ]);

            $table->foreign('media_id', 'media_publishing_groups_media_id_fk')
                ->references('id')->on('media')->onDelete('cascade');

            $table->foreign('publishing_group_id', 'media_publishing_groups_publishing_group_id_fk')
                ->references('id')->on('publishing_groups')->onDelete('cascade');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		if(Schema::hasTable('publishing_groups')) {
			Schema::table('media_publishing_groups', function (Blueprint $table) {
				$table->dropForeign('media_publishing_groups_media_id_fk');
				$table->dropForeign('media_publishing_groups_publishing_group_id_fk');
			});

			Schema::drop('media_publishing_groups');
		}
	}
}
