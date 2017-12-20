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
            $table->integer('site_id')->unsigned();
            $table->string('version',30);

			$table->string('path', 255)->index();
			$table->string('slug', 60)->nullable();

			$table->integer('revision_id', false, true)->unsigned()->nullable();

            // baum/nestedset fields
			$table->integer('parent_id')->nullable();
			$table->integer('lft')->nullable();
			$table->integer('rgt')->nullable();
			$table->integer('depth')->nullable();

            // Keep track
            $table->integer('created_by')->nullable();
            $table->integer('updated_by')->nullable();
            $table->timestamps();
            $table->timestamp('deleted_at')->nullable();

			$table->index(['site_id','version','lft'], 'idx_site_id_version_path');
            $table->foreign('revision_id', 'fk_revision_id')->references('id')->on('revisions')->onDelete('restrict');
            $table->foreign('site_id', 'fk_site_id')->references('id')->on('sites')->onDelete('restrict');
            $table->index('updated_at', 'idx_updated_at');
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
            $table->dropIndex('idx_updated_at');
            $table->dropForeign('fk_site_id');
            $table->dropForeign('fk_revision_id');
            $table->dropIndex('idx_site_id_version_path');
        });

		Schema::drop('pages');
	}
}
