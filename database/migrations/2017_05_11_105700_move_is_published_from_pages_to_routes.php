<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class MoveIsPublishedFromPagesToRoutes extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('routes', function(Blueprint $table) {
            $table->boolean('is_active')->default(0)->after('slug')->index();
        });

        DB::update('UPDATE routes INNER JOIN pages ON routes.page_id = pages.id SET routes.is_active = pages.is_published');

        Schema::table('pages', function(Blueprint $table) {
            $table->dropColumn('is_published');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('pages', function(Blueprint $table) {
            $table->boolean('is_published')->default(0)->index();
        });

        DB::update('UPDATE pages INNER JOIN routes ON pages.id = routes.page_id SET pages.is_published = routes.is_active');

        Schema::table('routes', function(Blueprint $table) {
            $table->dropColumn('is_active');
        });
    }
}
