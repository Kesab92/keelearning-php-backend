<?php

namespace App\Console\Commands\OneOff;

use App\Models\App;
use App\Models\User;
use App\Models\UserPermission;
use Illuminate\Console\Command;

/**
 * Class CacheStats.
 */
class MigrateDemoAdminsToUserReadonly extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'users:admin:demo:migrateuserreadonly';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Make sure all demo admin users only have readonly access to users';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $users = User::where('app_id', App::ID_KEEUNIT_DEMO)
            ->where('is_admin',1)
            ->get();
        foreach($users as $user) {
            $userManagementPermission = $user->permissions()->where('permission', 'users')->first();
            if($userManagementPermission) {
                $this->info('Deleting ' . $userManagementPermission->toJson());
                $userManagementPermission->delete();
            }
            $userReadonlyPermission = new UserPermission();
            $userReadonlyPermission->user_id = $user->id;
            $userReadonlyPermission->permission = 'users-readonly';
            $userReadonlyPermission->save();
        }
        $this->info('Done');
    }
}
