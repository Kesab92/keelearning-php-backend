<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddAnalyticsEventsTagTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('analytics_event_user_tag', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('analytics_event_id')->index();
            $table->unsignedBigInteger('tag_id')->index();
        });
        Schema::create('analytics_event_foreign_tag', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('analytics_event_id')->index();
            $table->unsignedBigInteger('tag_id')->index();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('analytics_event_user_tag');
        Schema::dropIfExists('analytics_event_foreign_tag');
    }
}
