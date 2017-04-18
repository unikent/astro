<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddLayoutFieldsToPagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('pages', function (Blueprint $table) {
            $table->string('layout_name')->after('is_site')->nullable();
            $table->integer('layout_version')->after('layout_name')->nullable();
        });

        DB::update("UPDATE pages SET layout_name = 'astro17', layout_version = 1");

        Schema::table('pages', function (Blueprint $table) {
            $table->string('layout_name')->nullable(false)->change();
            $table->integer('layout_version')->nullable(false)->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('pages', function (Blueprint $table) {
            $table->dropColumn('layout_name');
            $table->dropColumn('layout_version');
        });
    }
}
