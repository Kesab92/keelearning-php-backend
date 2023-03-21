<?php

use App\Models\App;
use App\Models\User;
use App\Models\UserRole;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddMainAdminRoles extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('user_roles', function (Blueprint $table) {
            $table->boolean('is_main_admin')->default(false)->index();
        });

        $apps = App::get();
        foreach ($apps as $app) {
            $role = new UserRole();
            $role->app_id = $app->id;
            $role->name = 'Hauptadmin';
            $role->description = 'Ein Hauptadmin verfügt über alle Rechte und kann anderen Benutzern Rechte zuweisen und wieder entziehen.';
            $role->is_main_admin = true;
            $role->save();

            // main admins can have neither permission nor TAG restrictions
            $mainAdmins = User::ofApp($app->id)->where('is_main_contact', true)->get();
            foreach ($mainAdmins as $mainAdmin) {
                $mainAdmin->tagRightsRelation()->detach();
                $mainAdmin->permissions()->delete();
                $mainAdmin->user_role_id = $role->id;
                $mainAdmin->save();
            }
        }

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('is_main_contact');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('is_main_contact')->default(false)->index();
        });
        $users = User::whereHas('role', function ($query) {
            $query->where('is_main_admin', true);
        })->update([
            'is_main_contact' => true,
            'user_role_id' => null,
        ]);
        UserRole::where('is_main_admin', true)->delete();
        Schema::table('user_roles', function (Blueprint $table) {
            $table->dropColumn('is_main_admin');
        });
    }
}
