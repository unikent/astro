<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRevisionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('revisions', function(Blueprint $table) {
            $table->increments('id');
            $table->integer('page_content_id')->unsigned()->index();
            $table->string('title');
            $table->string('layout_name');
            $table->integer('layout_version');

            $table->mediumText('bake');

            $table->string('type')->default('published');
            $table->integer('created_by')->nullable();
            $table->integer('updated_by')->nullable();
            $table->timestamps();

            $table->foreign('page_content_id', 'page_content_id_fk')->references('id')->on('page_content');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('revisions', function(Blueprint $table) {
            $table->dropForeign('page_content_id_fk');
        });
        Schema::drop('revisions');
    }
}
