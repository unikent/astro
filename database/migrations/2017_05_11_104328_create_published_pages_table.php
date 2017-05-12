<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePublishedPagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('published_pages', function(Blueprint $table) {
            $table->increments('id');
            $table->integer('page_id')->unsigned();

            $table->mediumText('bake');

            $table->integer('created_by')->nullable();
            $table->integer('updated_by')->nullable();
            $table->timestamps();

            $table->foreign('page_id', 'published_pages_page_id_fk')->references('id')->on('pages')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('published_pages', function(Blueprint $table) {
            $table->dropForeign('published_pages_page_id_fk');
        });

        Schema::drop('published_pages');
    }
}
