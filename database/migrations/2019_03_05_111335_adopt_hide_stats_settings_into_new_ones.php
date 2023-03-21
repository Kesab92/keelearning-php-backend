<?php

use App\Models\AppSetting;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AdoptHideStatsSettingsIntoNewOnes extends Migration
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
            $settings = AppSetting::where('key', 'hide_stats')->get();
            foreach ($settings as $setting) {
                $setting->key = 'hide_stats_quiz_challenge';
                $setting->save();

                $newSetting = new AppSetting();
                $newSetting->app_id = $setting->app_id;
                $newSetting->key = 'hide_stats_training';
                $newSetting->value = $setting->value;
                $newSetting->save();
            }
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
        DB::transaction(function (Blueprint $table) {
            $settings = AppSetting::where('key', 'hide_stats_quiz_challenge')->get();
            foreach ($settings as $setting) {
                $setting->key = 'hide_stats';
                $setting->save();
            }

            AppSetting::where('key', 'hide_stats_training')->delete();
        });
    }
}
