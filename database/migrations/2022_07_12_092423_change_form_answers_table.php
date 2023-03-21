<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeFormAnswersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('form_answers', function (Blueprint $table) {
            $table->unsignedInteger('foreign_type')->nullable(true);
            $table->unsignedInteger('foreign_id')->nullable(true);
            $table->index(['foreign_type', 'foreign_id', 'user_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('form_answers', function (Blueprint $table) {
            $table->dropIndex(['foreign_type', 'foreign_id', 'user_id']);
            $table->dropColumn('foreign_type');
            $table->dropColumn('foreign_id');
        });
    }
}
