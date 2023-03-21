<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAnalyticsEventsTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('analytics_events', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('app_id')->references('id')->on('apps');
            $table->unsignedBigInteger('user_id')->references('id')->on('users')->nullable();
            $table->unsignedBigInteger('foreign_id')->nullable();
            $table->unsignedSmallInteger('foreign_type')->nullable();
            $table->unsignedSmallInteger('type');
            $table->timestamp('created_at');
        });
        Schema::create('analytics_event_tag', function (Blueprint $table) {
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
        Schema::dropIfExists('analytics_events');
        Schema::dropIfExists('analytics_event_tag');
    }
}
