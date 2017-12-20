<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMedia extends Migration
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
			$table->string('hash')->index();
			$table->string('filename')->index();

			$table->integer('filesize')->index();

			$table->string('format')->nullable();
			$table->string('mime_type')->nullable();
			$table->integer('width')->nullable();
			$table->integer('height')->nullable();
			$table->float('aspect_ratio', 8, 3)->nullable();
			$table->float('duration', 8, 2)->nullable();

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
