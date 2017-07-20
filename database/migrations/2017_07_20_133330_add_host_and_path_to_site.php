<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddHostAndPathToSite extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('sites', function (Blueprint $table) {
            $table->string('host');
            $table->string('path')->default('');
            $table->unique(['host', 'path'], 'uk_host_path');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('sites', function( Blueprint $table) {
            $table->dropUnique('uk_host_path');
           $table->dropColumn('host');
           $table->dropColumn('path');
        });
    }
}
