<?php

namespace App\Console\Commands;

use App\Models\App;
use App\Samba\Data\CreateAccount;
use App\Samba\Samba;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class CreateMissingSambaAccounts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'samba:createmissing';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Creates samba accounts for all apps, if they don\'t exist already';

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
     *  Creates samba accounts for all apps which don't have one already.
     */
    public function handle()
    {
        foreach (App::whereNull('samba_id')->get() as $app) {
            /* @var App $app */
            $this->info('Adding account for '.$app->name);
            $username = $app->id.'-'.App::SLUGS[$app->id];
            $password = Str::random(32);
            $createAccount = new CreateAccount();
            $createAccount->setUsername($username);
            $createAccount->setPassword($password);
            $createAccount->setEmail($app->id.'-'.app()->env.'-mainz@keeunit.de');
            $createAccount->setFirstName($app->id.'-'.App::SLUGS[$app->id]);
            $createAccount->setLastName(' ');
            $language = $app->getLanguage();
            if ($language === 'de_formal') {
                $language = 'de';
            }
            $createAccount->setLanguage($language);
            $data = Samba::forCustomer(-1)->createAccount($createAccount);

            $app->samba_id = $data['id'];
            $app->samba_token = base64_encode($username.':'.$password);
            $app->save();
        }
    }
}
