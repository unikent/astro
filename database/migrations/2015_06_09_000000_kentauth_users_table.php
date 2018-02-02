<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Config;

class KentauthUsersTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('users', function (Blueprint $table) {
			$table->increments('id');
			$table->string('username')->unique();
            $table->string('password');
			$table->string('name');
			$table->string('email');
			$table->string('role');
            $table->string('api_token')->unique();
            $table->text('settings');
			$table->integer('created_by')->index()->nullable();
			$table->integer('updated_by')->index()->nullable();
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
		Schema::drop('users');
	}

}
