<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RenameGroupsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     * @throws Throwable
     */
    public function up()
    {
        DB::transaction(function () {
            Schema::table('group_users', function (Blueprint $table) {
                $table->renameColumn('group_id', 'quiz_team_id');
                $table->dropIndex(['group_id']);
                $table->dropIndex(['user_id']);
            });
            Schema::table('groups', function (Blueprint $table) {
                $table->dropIndex(['app_id']);
            });
            Schema::table('competitions', function (Blueprint $table) {
                $table->renameColumn('group_id', 'quiz_team_id');
            });
            Schema::table('tests', function (Blueprint $table) {
                $table->renameColumn('group_id', 'quiz_team_id');
            });

            Schema::rename('groups', 'quiz_teams');
            Schema::rename('group_users', 'quiz_team_members');

            Schema::table('quiz_team_members', function (Blueprint $table) {
                $table->index('quiz_team_id');
                $table->index('user_id');
            });
            Schema::table('quiz_teams', function (Blueprint $table) {
                $table->index('app_id');
                $table->integer('owner_id')->nullable(true)->change();
            });
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     * @throws Throwable
     */
    public function down()
    {
        DB::transaction(function () {
            Schema::table('quiz_team_members', function (Blueprint $table) {
                $table->renameColumn('quiz_team_id', 'group_id');
                $table->dropIndex(['quiz_team_id']);
                $table->dropIndex(['user_id']);
            });
            Schema::table('quiz_teams', function (Blueprint $table) {
                $table->dropIndex(['app_id']);
            });
            Schema::table('competitions', function (Blueprint $table) {
                $table->renameColumn('quiz_team_id', 'group_id');
            });
            Schema::table('tests', function (Blueprint $table) {
                $table->renameColumn('quiz_team_id', 'group_id');
            });

            Schema::rename('quiz_teams', 'groups');
            Schema::rename('quiz_team_members', 'group_users');

            Schema::table('group_users', function (Blueprint $table) {
                $table->index('group_id');
                $table->index('user_id');
            });
            Schema::table('groups', function (Blueprint $table) {
                $table->index('app_id');
                $table->text('owner_id')->nullable(false)->change();
            });
        });
    }
}
