<?php

use App\Models\AppSetting;
use Illuminate\Database\Migrations\Migration;

class RenameHideStatisticNamesSetting extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        AppSetting::where('key', 'hide_statistic_names')->update(['key' => 'hide_personal_data']);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        AppSetting::where('key', 'hide_personal_data')->update(['key' => 'hide_statistic_names']);
    }
}
