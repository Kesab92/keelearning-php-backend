<?php

use Illuminate\Database\Migrations\Migration;

class ChangeRightValuesInUserRoleRightsTable extends Migration
{
    private array $changedRights = [
        'groups-personaldata' => 'quizteams-personaldata',
        'groups-showemails' => 'quizteams-showemails',
    ];
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        foreach ($this->changedRights as $oldRight => $newRight){
            DB::table('user_role_rights')
                ->where('right', $oldRight)
                ->update([
                    "right" => $newRight
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
        foreach ($this->changedRights as $oldRight => $newRight) {
            DB::table('user_role_rights')
                ->where('right', $newRight)
                ->update([
                    "right" => $oldRight
                ]);
        }
    }
}
