<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTagsIndizes extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tag_groups', function (Blueprint $table) {
            $table->index('app_id');
        });
        Schema::table('tags', function (Blueprint $table) {
            $table->index('app_id');
            $table->index('tag_group_id');
            $table->index('deleted_at');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('tag_groups', function (Blueprint $table) {
            $table->dropIndex('app_id');
        });
        Schema::table('tags', function (Blueprint $table) {
            $table->dropIndex('app_id');
            $table->dropIndex('tag_group_id');
            $table->dropIndex('deleted_at');
        });
    }
}
