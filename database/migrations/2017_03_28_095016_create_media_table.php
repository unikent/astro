<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMediaTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('media', function(Blueprint $table) {
			$table->increments('id');

			$table->enum('type', ['image', 'document', 'video', 'audio'])->default('image');

			$table->string('name')->index();
			$table->integer('size')->index();
			$table->string('sha1', 40)->index();

			$table->string('format')->index()->nullable();
			$table->string('mime_type')->index()->nullable();

			$table->integer('width')->index()->nullable();
			$table->integer('height')->index()->nullable();
			$table->float('aspect_ratio', 8, 3)->index()->nullable();

			$table->float('duration', 8, 2)->index()->nullable();

			$table->integer('created_by')->unsigned()->nullable();
			$table->integer('updated_by')->unsigned()->nullable();

			$table->timestamps();
			$table->softDeletes();
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('media');
	}
}
