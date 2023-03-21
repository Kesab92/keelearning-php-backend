<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSignupSelectableColumnToTagGroupsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tag_groups', function (Blueprint $table) {
            $table->boolean('signup_selectable');
            $table->index('signup_selectable');
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
            $table->dropColumn('signup_selectable');
        });
    }
}
