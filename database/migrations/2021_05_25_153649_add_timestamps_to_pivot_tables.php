<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTimestampsToPivotTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('category_tag', function (Blueprint $table) {
            $table->timestamps();
        });
        Schema::table('categorygroup_tag', function (Blueprint $table) {
            $table->timestamps();
        });
        Schema::table('competition_tag', function (Blueprint $table) {
            $table->timestamps();
        });
        Schema::table('news_tag', function (Blueprint $table) {
            $table->timestamps();
        });
        Schema::table('tag_user', function (Blueprint $table) {
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('category_tag', function (Blueprint $table) {
            $table->dropTimestamps();
        });
        Schema::table('categorygroup_tag', function (Blueprint $table) {
            $table->dropTimestamps();
        });
        Schema::table('competition_tag', function (Blueprint $table) {
            $table->dropTimestamps();
        });
        Schema::table('news_tag', function (Blueprint $table) {
            $table->dropTimestamps();
        });
        Schema::table('tag_user', function (Blueprint $table) {
            $table->dropTimestamps();
        });
    }
}
