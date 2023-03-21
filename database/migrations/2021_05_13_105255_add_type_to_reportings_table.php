<?php

use App\Models\Reporting;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTypeToReportingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('reportings', function (Blueprint $table) {
            $table->dropColumn('group_ids');
            $table->integer('type')->index();
        });
        DB::table('reportings')
            ->update(['type' => Reporting::TYPE_QUIZ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('reportings', function (Blueprint $table) {
            $table->text('group_ids');
            $table->dropColumn('type');
        });
    }
}
