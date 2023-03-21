<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CleanupAnalyticEventsIndex extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('analytics_events', function (Blueprint $table) {
            $table->dropIndex('existing_check_index');
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
            $table->index(['type', 'foreign_id', 'foreign_type', 'user_id'], 'existing_check_index');
        });
    }
}
