<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdateRoutesPostPrototype extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('routes', function (Blueprint $table) {
            $table->integer('page_id')->unsigned()->change();
            $table->foreign('page_id', 'page_id_fk')->references('id')->on('pages');

            $table->string('slug')->nullable(true)->change();

            $table->boolean('is_canonical')->boolean()->default(false)->nullable();
        });

        DB::update("UPDATE routes SET is_canonical = true");

        Schema::table('routes', function (Blueprint $table) {
            $table->boolean('is_canonical')->nullable(false)->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('routes', function (Blueprint $table) {
            $table->dropForeign('page_id_fk');

            $table->string('slug')->nullable(false)->change();

            $table->dropColumn('is_canonical');
        });
    }
}
