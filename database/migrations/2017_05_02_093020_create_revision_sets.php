<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * revision_sets tracks relations between revisions.
 */
class CreateRevisionSets extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('revision_sets', function (Blueprint $table){
            $table->increments('id');
            $table->integer('site_id')->unsigned();
            $table->timestamps();
            $table->foreign('site_id', 'sites_id_fk')->references('id')->on('sites');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('revision_sets', function(Blueprint $table) {
            $table->dropForeign('sites_id_fk');
        });

        Schema::dropIfExists('revision_sets');
    }
}
