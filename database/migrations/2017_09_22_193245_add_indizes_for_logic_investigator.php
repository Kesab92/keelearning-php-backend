<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIndizesForLogicInvestigator extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tag_user', function (Blueprint $table) {
            $table->index('user_id');
        });
        Schema::table('questions', function (Blueprint $table) {
            $table->index(['visible', 'category_id']);
        });
        Schema::table('categories', function (Blueprint $table) {
            $table->index(['app_id', 'active']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('tag_user', function (Blueprint $table) {
            $table->dropIndex(['user_id']);
        });
        Schema::table('questions', function (Blueprint $table) {
            $table->dropIndex(['visible', 'category_id']);
        });
        Schema::table('categories', function (Blueprint $table) {
            $table->dropIndex(['app_id', 'active']);
        });
    }
}
