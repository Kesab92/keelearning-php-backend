<?php

namespace App\Console\Commands\OneOff;

use App\Models\App;
use App\Services\AppProfileSettings;
use Illuminate\Console\Command;

class UseAppAuthLogoInAllApps extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'profilesettings:enableauthlogo';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Copies the mobile logo to the auth logo for all apps';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $apps = App::get();
        if(date('Y-m-d') > '2020-11-06') {
            $this->info('Command not active');
            return;
        }
        foreach($apps as $app) {
            $profileSettings = $app->getDefaultAppProfile();
            $appProfileSettings = new AppProfileSettings($profileSettings->id);
            $mobileLogo = $appProfileSettings->getValue('app_logo');
            $authLogo = $appProfileSettings->getValue('app_logo_auth');
            if($mobileLogo && !$authLogo) {
                $appProfileSettings->setValue('app_logo_auth', $mobileLogo);
            }
        }
    }
}
