<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePages extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('pages', function (Blueprint $table) {
			$table->increments('id');

			$table->string('path', 255)->index();
			$table->string('slug', 60)->nullable();

            $table->integer('site_id')->unsigned();

			$table->integer('draft_id', false, true)->unsigned()->nullable();
            $table->integer('published_id', false, true)->nullable();

			$table->integer('parent_id')->nullable();
			$table->integer('lft')->nullable();
			$table->integer('rgt')->nullable();
			$table->integer('depth')->nullable();

            $table->foreign('published_id', 'published_id_fk')->references('id')->on('revisions')->onDelete('restrict');
            $table->foreign('site_id', 'site_id_fk')->references('id')->on('sites');
            $table->foreign('draft_id', 'draft_id_fk')->references('id')->on('revisions')->onDelete('restrict');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
        Schema::table('pages', function(Blueprint $table) {
            $table->dropForeign('site_id_fk');
            $table->dropForeign('draft_id_fk');
            $table->dropForeign('published_id_fk');
        });

		Schema::drop('pages');
	}
}
