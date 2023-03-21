<?php

namespace App\Console\Commands;

use App\Models\App;
use App\Models\User;
use App\Models\UserPermission;
use App\Models\UserRole;
use App\Models\UserRoleRight;
use Illuminate\Console\Command;

class MigratePermissionsToRights extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'migrate:permissionstorights';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Migrates old permissions to new user role system.';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $apps = App::get();
        foreach ($apps as $app) {
            $this->info('Migrating permissions of app #' . $app->id);

            $adminUsers = User::ofApp($app->id)
                ->where('is_admin', true)
                ->whereNull('user_role_id')
                ->with('permissions')
                ->get();
            $this->line('Migrating ' . $adminUsers->count() . ' users…');

            $newRoles = collect();
            $adminUserCount = 0;
            foreach ($adminUsers as $adminUser) {
                if ($adminUser->isSuperAdmin()) {
                    continue;
                }
                $adminUserCount += 1;
                $adminUserRights = collect();
                foreach (UserRoleRight::RIGHT_TYPES as $right) {
                    if (UserPermission::hasEquivalentRight($adminUser, $right)) {
                        $adminUserRights->push($right);
                    }
                }
                if (UserPermission::hadPermission($adminUser, 'manage-user_rights')) {
                    $this->warn('User "' . $adminUser->username . '" #' . $adminUser->id . ' lost his ability to manage user permissions!');
                }
                if (
                    UserPermission::hadPermission($adminUser, 'import')
                    && !UserPermission::hadPermission($adminUser, 'index_cards')
                    && !UserPermission::hadPermission($adminUser, 'questions')
                    && !UserPermission::hadPermission($adminUser, 'users')
                ) {
                    $this->warn('User "' . $adminUser->username . '" #' . $adminUser->id . ' lost his ability to access imports!');
                }
                if (!$adminUserRights->count()) {
                    $this->warn('User "' . $adminUser->username . '" #' . $adminUser->id . ' has no role due to lack of rights!');
                } else {
                    $id = $adminUserRights->join(',');
                    if (!$newRoles->get($id)) {
                        $newRole = collect([
                            'rights' => $adminUserRights,
                            'user_ids' => collect($adminUser->id),
                        ]);
                        $newRoles->put($id, $newRole);
                    } else {
                        $newRoles->get($id)->get('user_ids')->push($adminUser->id);
                    }
                }
            }

            $iterator = 1;
            $newRoles->each(function ($newRole) use ($app, &$iterator) {
                $userRole = new UserRole;
                $userRole->app_id = $app->id;
                $userRole->name = 'Admin-Rolle #' . str_pad($iterator, 2, '0', STR_PAD_LEFT);
                $userRole->description = 'Automatisch durch keelearning generierte Admin-Rolle.';
                $userRole->save();
                $newRole['rights']->each(function ($right) use ($userRole) {
                    $userRole->rights()->create(['right' => $right]);
                });
                User::whereIn('id', $newRole->get('user_ids'))
                    ->update(['user_role_id' => $userRole->id]);
                $iterator += 1;
            });

            if ($newRoles->count()) {
                $this->line('Created and assigned ' . $newRoles->count() . ' roles to ' . $adminUserCount . ' users.');
            } else {
                $this->warn('No roles were created!');
            }

            $this->line('Migrated permissions of app #' . $app->id);
        }
    }
}
