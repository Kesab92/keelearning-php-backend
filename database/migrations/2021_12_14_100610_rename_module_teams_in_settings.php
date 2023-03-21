<?php

use Illuminate\Database\Migrations\Migration;

class RenameModuleTeamsInSettings extends Migration
{
    private array $changedSettings = [
        'module_teams' => 'module_quiz_teams',
    ];

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        foreach ($this->changedSettings as $oldSetting => $newSetting) {
            DB::table('app_settings')
                ->where('key', $oldSetting)
                ->update([
                    "key" => $newSetting
                ]);
            DB::table('app_profile_settings')
                ->where('key', $oldSetting)
                ->update([
                    "key" => $newSetting
                ]);
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        foreach ($this->changedSettings as $oldSetting => $newSetting) {
            DB::table('app_settings')
                ->where('key', $newSetting)
                ->update([
                    "key" => $oldSetting
                ]);
            DB::table('app_profile_settings')
                ->where('key', $newSetting)
                ->update([
                    "key" => $oldSetting
                ]);
        }
    }
}
