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
		Schema::create('media', function (Blueprint $table) {
			$table->increments('id');

			$table->enum('type', ['image', 'document', 'video', 'audio'])->default('image');
			$table->string('path')->index();

			$table->string('file_name')->index();
			$table->string('file_mime')->index()->nullable();
			$table->integer('file_size')->index();

			$table->text('meta');

			$table->string('sha1', 40)->index();

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
