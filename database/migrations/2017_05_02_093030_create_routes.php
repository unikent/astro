<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRoutes extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('routes', function (Blueprint $table) {
			$table->increments('id');

			$table->string('path', 255)->index();
			$table->string('slug', 60)->nullable();
            $table->boolean('is_canonical')->boolean()->default(false);

			$table->integer('page_id')->unsigned();
            $table->integer('site_id')->unsigned()->nullable();

			$table->integer('parent_id')->nullable();
			$table->integer('lft')->nullable();
			$table->integer('rgt')->nullable();
			$table->integer('depth')->nullable();

            $table->foreign('site_id', 'site_id_fk')->references('id')->on('sites');
            $table->foreign('page_id', 'page_id_fk')->references('id')->on('pages')->onDelete('cascade');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
        Schema::table('routes', function(Blueprint $table) {
            $table->dropForeign('site_id_fk');
            $table->dropForeign('page_id_fk');
        });

		Schema::drop('routes');
	}
}
