<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePublishingGroupsUsers extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('publishing_groups_users', function (Blueprint $table){
            $table->increments('id');
            $table->integer('user_id')->unsigned()->index();
            $table->integer('publishing_group_id')->unsigned()->index();

            $table->unique([ 'user_id', 'publishing_group_id' ]);

            $table->foreign('user_id', 'publishing_groups_users_user_id_fk')
                ->references('id')->on('users')->onDelete('cascade');

            $table->foreign('publishing_group_id', 'publishing_groups_users_publishing_group_id_fk')
                ->references('id')->on('publishing_groups')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
    	if(Schema::hasTable('publishing_groups_users')) {
			Schema::table('publishing_groups_users', function (Blueprint $table) {
				$table->dropForeign('publishing_groups_users_user_id_fk');
				$table->dropForeign('publishing_groups_users_publishing_group_id_fk');
			});

			Schema::dropIfExists('publishing_groups_users');
		}
    }
}
