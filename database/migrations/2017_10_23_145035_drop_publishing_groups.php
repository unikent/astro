<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class DropPublishingGroups extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
    	Schema::dropIfExists('publishing_groups_users');
    	Schema::dropIfExists('media_publishing_groups');
    	Schema::table('sites', function(Blueprint $table) {
    		$table->dropForeign('sites_publishing_group_id_fk');
    		$table->dropColumn('publishing_group_id');
		});
    	Schema::dropIfExists('publishing_groups');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
