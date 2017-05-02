<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSites extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sites', function (Blueprint $table){
            $table->increments('id');
            $table->string('name');
            $table->integer('publishing_group_id')->unsigned()->index();

            $table->timestamps();
            $table->softDeletes();

            $table->foreign('publishing_group_id', 'sites_publishing_group_id_fk')->references('id')->on('publishing_groups');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('sites', function(Blueprint $table) {
            $table->dropForeign('sites_publishing_group_id_fk');
        });

        Schema::dropIfExists('sites');
    }
}
