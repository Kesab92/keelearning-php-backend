<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddUserTagsIndizes extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('user_tag_rights', function (Blueprint $table) {
            $table->index('user_id');
            $table->index('tag_id');
        });
        Schema::table('tag_user', function (Blueprint $table) {
            $table->index('tag_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('user_tag_rights', function (Blueprint $table) {
            $table->dropIndex('user_id');
            $table->dropIndex('tag_id');
        });
        Schema::table('tag_user', function (Blueprint $table) {
            $table->dropIndex('tag_id');
        });
    }
}
