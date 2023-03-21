<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIndizesToUserQuestionDataTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('user_question_data', function (Blueprint $table) {
            $table->unsignedBigInteger('app_id')->references('id')->on('apps')->after('id');
            $table->index(['user_id', 'question_id']);
            $table->index(['question_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('user_question_data', function (Blueprint $table) {
            $table->dropColumn('app_id');
            $table->dropIndex(['user_id', 'question_id']);
            $table->dropIndex(['question_id']);
        });
    }
}
