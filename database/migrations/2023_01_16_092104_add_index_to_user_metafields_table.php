<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIndexToUserMetafieldsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('user_metafields', function (Blueprint $table) {
            $table->index(['user_id', 'key']);
            $table->dropIndex(['user_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('user_metafields', function (Blueprint $table) {
            $table->index('user_id');
            $table->dropIndex(['user_id', 'key']);
        });
    }
}
