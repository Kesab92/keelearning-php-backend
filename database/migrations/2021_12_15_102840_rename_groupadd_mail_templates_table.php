<?php

use Illuminate\Database\Migrations\Migration;

class RenameGroupaddMailTemplatesTable extends Migration
{
    private array $changedMailTemplateTypes = [
        'GroupAdd' => 'QuizTeamAdd',
    ];
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        foreach ($this->changedMailTemplateTypes as $oldType => $newType){
            DB::table('mail_templates')
                ->where('type', $oldType)
                ->update([
                    "type" => $newType
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
        foreach ($this->changedMailTemplateTypes as $oldType => $newType){
            DB::table('mail_templates')
                ->where('type', $newType)
                ->update([
                    "type" => $oldType
                ]);
        }
    }
}
