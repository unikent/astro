<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDeletedPagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('deleted_pages', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('revision_id')->unsigned();
            $table->string('path');

            // Keep track
            $table->integer('created_by')->nullable();
            $table->integer('updated_by')->nullable();
            $table->timestamps();

            $table->foreign('revision_id', 'deleted_pages_revisions_id_fk')->references('id')->on('revisions');
            $table->index('path', 'deleted_pages_path_idx');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('deleted_pages', function(Blueprint $table){
            $table->dropForeign('deleted_pages_revisions_id_fk');
            $table->dropIndex('deleted_pages_path_idx');
        });
        Schema::dropIfExists('deleted_pages');
    }
}
