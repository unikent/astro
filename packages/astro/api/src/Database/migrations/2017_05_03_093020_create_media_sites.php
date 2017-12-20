<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMediaSites extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('media_sites', function(Blueprint $table) {
			$table->increments('id');

            $table->integer('media_id')->unsigned()->index();
            $table->integer('site_id')->unsigned()->index();

            $table->unique([ 'media_id', 'site_id' ]);

            $table->foreign('media_id', 'media_sites_media_id_fk')
                ->references('id')->on('media')->onDelete('cascade');

            $table->foreign('site_id', 'media_sites_site_id_fk')
                ->references('id')->on('sites')->onDelete('cascade');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
        Schema::table('media_sites', function(Blueprint $table) {
            $table->dropForeign('media_sites_media_id_fk');
            $table->dropForeign('media_sites_site_id_fk');
        });

		Schema::drop('media_sites');
	}
}
