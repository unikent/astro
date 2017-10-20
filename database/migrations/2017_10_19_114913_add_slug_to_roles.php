<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddSlugToRoles extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('roles', function (Blueprint $table) {
            $table->string('slug', 100)->nullable();
            $table->unique('slug', 'ui_roles_slug');
            $table->dropUnique('roles_name_unique');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('roles', function (Blueprint $table) {
            $table->unique('roles_name_unique');
            $table->dropUnique('ui_roles_slug');
            $table->dropColumn('slug');
        });
    }
}
