<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeRepeatableColumnInTestsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tests', function (Blueprint $table) {
            $table->integer('attempts')->after('repeatable')->default(1);
        });
        DB::table('tests')->where('repeatable', true)->update(['attempts' => 0]);
        Schema::table('tests', function (Blueprint $table) {
            $table->dropColumn('repeatable');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('tests', function (Blueprint $table) {
            $table->boolean('repeatable')->after('attempts')->default(false);
        });
        DB::table('tests')
            ->where('attempts', '>', 1)
            ->orWhere('attempts', 0)
            ->update(['repeatable' => true]);
        Schema::table('tests', function (Blueprint $table) {
            $table->dropColumn('attempts');
        });
    }
}
