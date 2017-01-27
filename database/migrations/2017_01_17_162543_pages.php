<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class Pages extends Migration
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
			$table->string('slug', 60);

			$table->integer('page_id')->index();

			$table->integer('parent_id')->nullable();
			$table->integer('lft')->nullable();
			$table->integer('rgt')->nullable();
			$table->integer('depth')->nullable();
		});

		Schema::create('pages', function (Blueprint $table) {
			$table->increments('id');
			$table->string('title');
			$table->text('options');

			$table->boolean('key_page')->default(0);
			$table->boolean('published')->default(0);

			// Keep track
			$table->integer('created_by')->nullable();
			$table->integer('updated_by')->nullable();
			$table->timestamps();
		});

		Schema::create('blocks', function (Blueprint $table) {
			$table->increments('id');

			$table->integer('page_id')->index();

			$table->integer('parent_block')->default(0);
			$table->integer('order')->default(0);;
			$table->integer('section')->default(0);

			$table->string('type_guid');
			$table->text('fields');

			// Keep track
			$table->integer('created_by')->nullable();
			$table->integer('updated_by')->nullable();
			$table->timestamps();
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('routes');
		Schema::drop('pages');
		Schema::drop('blocks');
	}
}
