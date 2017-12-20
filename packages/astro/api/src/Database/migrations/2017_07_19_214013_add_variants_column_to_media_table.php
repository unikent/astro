<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddVariantsColumnToMediaTable extends Migration
{
	public function up()
	{
		Schema::table('media', function (Blueprint $table) {
			$table->text('variants')->nullable()->after('height');
		});
	}

	public function down()
	{
		Schema::table('media', function (Blueprint $table) {
			$table->dropColumn('variants');
		});
	}
}
