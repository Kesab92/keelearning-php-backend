<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RenameTodoDoneColumn extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('todolist_item_answers', function(Blueprint $table) {
            $table->renameColumn('is_checked', 'is_done');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('todolist_item_answers', function(Blueprint $table) {
            $table->renameColumn('is_done', 'is_checked');
        });
    }
}
