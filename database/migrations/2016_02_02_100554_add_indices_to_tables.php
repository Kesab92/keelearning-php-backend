<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddIndicesToTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->index('app_id');
        });

        Schema::table('game_question_answers', function (Blueprint $table) {
            $table->index('game_question_id');
            $table->index('user_id');
            $table->index('question_answer_id');
        });

        Schema::table('game_questions', function (Blueprint $table) {
            $table->index('game_round_id');
            $table->index('question_id');
        });

        Schema::table('game_rounds', function (Blueprint $table) {
            $table->index('game_id');
            $table->index('category_id');
        });

        Schema::table('games', function (Blueprint $table) {
            $table->index('app_id');
            $table->index('player1_id');
            $table->index('player2_id');
        });

        Schema::table('group_users', function (Blueprint $table) {
            $table->index('group_id');
            $table->index('user_id');
        });

        Schema::table('groups', function (Blueprint $table) {
            $table->index('app_id');
        });

        Schema::table('pages', function (Blueprint $table) {
            $table->index('app_id');
        });

        Schema::table('question_answers', function (Blueprint $table) {
            $table->index('question_id');
        });

        Schema::table('questions', function (Blueprint $table) {
            $table->index('app_id');
        });

        Schema::table('suggested_question_answers', function (Blueprint $table) {
            $table->index('suggested_question_id');
        });

        Schema::table('suggested_questions', function (Blueprint $table) {
            $table->index('app_id');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->index('app_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->dropIndex(['app_id']);
        });

        Schema::table('game_question_answers', function (Blueprint $table) {
            $table->dropIndex(['game_question_id']);
            $table->dropIndex(['user_id']);
            $table->dropIndex(['question_answer_id']);
        });

        Schema::table('game_questions', function (Blueprint $table) {
            $table->dropIndex(['game_round_id']);
            $table->dropIndex(['question_id']);
        });

        Schema::table('game_rounds', function (Blueprint $table) {
            $table->dropIndex(['game_id']);
            $table->dropIndex(['category_id']);
        });

        Schema::table('games', function (Blueprint $table) {
            $table->dropIndex(['app_id']);
            $table->dropIndex(['player1_id']);
            $table->dropIndex(['player2_id']);
        });

        Schema::table('group_users', function (Blueprint $table) {
            $table->dropIndex(['group_id']);
            $table->dropIndex(['user_id']);
        });

        Schema::table('groups', function (Blueprint $table) {
            $table->dropIndex(['app_id']);
        });

        Schema::table('pages', function (Blueprint $table) {
            $table->dropIndex(['app_id']);
        });

        Schema::table('question_answers', function (Blueprint $table) {
            $table->dropIndex(['question_id']);
        });

        Schema::table('questions', function (Blueprint $table) {
            $table->dropIndex(['app_id']);
        });

        Schema::table('suggested_question_answers', function (Blueprint $table) {
            $table->dropIndex(['suggested_question_id']);
        });

        Schema::table('suggested_questions', function (Blueprint $table) {
            $table->dropIndex(['app_id']);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['app_id']);
        });
    }
}
