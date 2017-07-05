<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class SwitchTextToMediumtext extends Migration
{
	public function up()
	{
		Schema::table('blocks', function (Blueprint $table) {
			$table->mediumText('fields')->change();
		});

		Schema::table('pages', function (Blueprint $table) {
			$table->mediumText('options')->change();
		});
	}

	public function down()
	{
		Schema::table('blocks', function (Blueprint $table) {
			$table->text('fields')->change();
		});

		Schema::table('pages', function (Blueprint $table) {
			$table->text('options')->change();
		});
	}
}
