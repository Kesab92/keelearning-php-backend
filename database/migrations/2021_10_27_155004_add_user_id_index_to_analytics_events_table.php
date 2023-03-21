<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddUserIdIndexToAnalyticsEventsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('analytics_events', function (Blueprint $table) {
            $table->index(['app_id', 'user_id'], 'app_user_index');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('analytics_events', function (Blueprint $table) {
            $table->dropIndex('app_user_index');
        });
    }
}
