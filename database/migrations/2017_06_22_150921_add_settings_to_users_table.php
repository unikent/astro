<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddSettingsToUsersTable extends Migration
{
	public function up()
	{
		Schema::table('users', function (Blueprint $table) {
			$table->text('settings')->after('api_token');
		});
	}

	public function down()
	{
		Schema::table('users', function (Blueprint $table) {
			$table->dropColumn('settings');
		});
	}
}
