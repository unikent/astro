<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddRegionFieldsToBlocksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('blocks', function (Blueprint $table) {
            $table->renameColumn('type', 'definition_name');
            $table->integer('definition_version')->after('type')->nullable();
            $table->string('region_name')->after('definition_version')->nullable();
        });

        DB::update("UPDATE blocks SET region_name = 'main', definition_version = 1");

        Schema::table('blocks', function (Blueprint $table) {
            $table->integer('definition_version')->nullable(false)->change();
            $table->string('region_name')->nullable(false)->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('blocks', function (Blueprint $table) {
            $table->renameColumn('definition_name', 'type');
            $table->dropColumn('definition_version');
            $table->dropColumn('region_name');
        });
    }
}
