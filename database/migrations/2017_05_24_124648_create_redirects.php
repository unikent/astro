<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRedirects extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('redirects', function(Blueprint $table) {
            $table->increments('id');

            $table->string('path')->unique();
            $table->integer('page_id')->unsigned();

            $table->timestamps();
            $table->foreign('page_id', 'redirects_page_id_fk')->references('id')->on('pages')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('redirects', function(Blueprint $table) {
            $table->dropForeign('redirects_page_id_fk');
        });

        Schema::drop('redirects');
    }
}
