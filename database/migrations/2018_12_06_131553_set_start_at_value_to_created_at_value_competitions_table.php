<?php

use App\Models\Competition;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class SetStartAtValueToCreatedAtValueCompetitionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $competitions = Competition::whereNull('start_at')->get();
        foreach ($competitions as $competition) {
            $date = $competition->created_at;
            $date->minute = 0;
            $date->second = 0;
            $date->hour = 0;

            $competition->start_at = $date;
            $competition->save();
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $competitions = Competition::whereColumn('created_at', 'start_at')->get();
        foreach ($competitions as $competition) {
            $competition->start_at = null;
            $competition->save();
        }
    }
}
