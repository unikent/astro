<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePageContent extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('page_content', function (Blueprint $table) {
			$table->increments('id');
			$table->string('title');
			$table->mediumText('options');
            $table->string('layout_name');
            $table->integer('layout_version');
            $table->integer('site_id')->unsigned()->index();
			// Keep track
			$table->integer('created_by')->nullable();
			$table->integer('updated_by')->nullable();
			$table->timestamps();
            $table->timestamp('deleted_at')->nullable()->index();
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('page_content');
	}
}
