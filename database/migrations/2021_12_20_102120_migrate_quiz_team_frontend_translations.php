<?php

use Illuminate\Database\Migrations\Migration;

class MigrateQuizTeamFrontendTranslations extends Migration
{
    private array $changedTranslationKeys = [
        'block_mail_group' => 'block_mail_quiz_team',
    ];

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        foreach ($this->changedTranslationKeys as $oldKey => $newKey) {
            DB::table('frontend_translations')
                ->where('key', $oldKey)
                ->update([
                    "key" => $newKey
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
        foreach ($this->changedTranslationKeys as $oldKey => $newKey) {
            DB::table('frontend_translations')
                ->where('key', $newKey)
                ->update([
                    "key" => $oldKey
                ]);
        }
    }
}
