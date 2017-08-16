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
            $table->integer('revision_set_id')->unsigned();
            $table->string('title');
            $table->string('layout_name');
            $table->integer('layout_version');
            $table->mediumText('blocks');
            $table->mediumText('options');
            $table->integer('created_by')->nullable();
            $table->integer('updated_by')->nullable();
            $table->timestamps();
            $table->foreign('revision_set_id', 'revision_sets_id_fk')->references('id')->on('revision_sets');
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
            $table->dropForeign('revision_sets_id_fk');
        });
        Schema::drop('revisions');
    }
}
