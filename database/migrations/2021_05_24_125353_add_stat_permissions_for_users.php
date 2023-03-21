<?php

use App\Models\User;
use App\Models\UserPermission;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddStatPermissionsForUsers extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $usersWithCoursesPermission = User::whereHas('permissions', function ($q) {
            $q->where('permission', 'courses');
        })
            ->get();

        $usersWithTestsPermission = User::whereHas('permissions', function ($q) {
            $q->where('permission', 'tests');
        })
            ->get();

        foreach ($usersWithCoursesPermission as $user) {
            $p = new UserPermission();
            $p->user_id = $user->id;
            $p->permission = 'courses-stats';
            $p->save();
        }

        foreach ($usersWithTestsPermission as $user) {
            $p = new UserPermission();
            $p->user_id = $user->id;
            $p->permission = 'tests-stats';
            $p->save();
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        UserPermission::where('permission', 'courses-stats')
            ->orWhere('permission', 'tests-stats')
            ->delete();
    }
}
