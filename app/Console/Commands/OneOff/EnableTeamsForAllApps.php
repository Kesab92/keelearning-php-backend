<?php

namespace App\Console\Commands\OneOff;

use App\Models\App;
use App\Services\AppProfileSettings;
use Illuminate\Console\Command;

class EnableTeamsForAllApps extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'profilesettings:enableteams';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Enables the team module for all apps';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $apps = App::get();
        foreach($apps as $app) {
            $profileSettings = $app->getDefaultAppProfile();
            $appProfileSettings = new AppProfileSettings($profileSettings->id);
            $appProfileSettings->setValue('module_quiz_teams', 1);
        }
    }
}
