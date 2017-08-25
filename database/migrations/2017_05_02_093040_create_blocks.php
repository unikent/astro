<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBlocks extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('blocks', function (Blueprint $table) {
			$table->increments('id');

			$table->integer('page_id')->unsigned()->index();
			$table->integer('order')->default(0);;

			$table->string('definition_name');
            $table->integer('definition_version');
            $table->string('region_name')->index();

			$table->mediumText('fields');

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
        Schema::table('blocks', function(Blueprint $table) {
        });
		Schema::drop('blocks');
	}
}
