<?php

use Illuminate\Database\Migrations\Migration;

class RenameBlockMailGroupInSettings extends Migration
{
    private array $changedSettings = [
        'block_mail_group' => 'block_mail_quiz_team',
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
        }
    }
}
