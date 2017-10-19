<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddSlugToPermissions extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('permissions', function (Blueprint $table) {
			$table->string('slug', 100)->nullable();
			$table->unique('slug', 'ui_perms_slug');
            $table->dropUnique('permissions_name_unique');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('permissions', function (Blueprint $table) {
            $table->unique('permissions_name_unique');
			$table->dropUnique('ui_perms_slug');
			$table->dropColumn('slug');
        });
    }
}
