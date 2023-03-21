<?php

namespace App\Console\Commands\OneOff;

use App\Models\App;
use App\Services\AppProfileSettings;
use App\Services\AppSettings;
use Illuminate\Console\Command;

class EnableVouchersForAllApps extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'profilesettings:enablevouchers';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Enables the voucher module for all apps which have it available';

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
            $appSettings = new AppSettings($app->id);
            if($appSettings->getValue('module_vouchers')) {
                $appProfileSettings->setValue('module_vouchers', 1);
            }
        }
    }
}
