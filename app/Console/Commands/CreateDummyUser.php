<?php

namespace App\Console\Commands;

use App\Models\App;
use App\Models\User;
use Hash;
use Illuminate\Console\Command;
use Str;

class CreateDummyUser extends Command
{
    /** The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:dummy';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Creates dummy user for all apps, if it does not exists';

    public function handle()
    {
        foreach (App::all() as $app) {
            $count = User::where('app_id', $app->id)
                ->where('is_dummy', '>', 0)
                ->count();

            $this->info('Checking App(#'.$app->id.'): It has '.$count.' dummy accounts');
            if ($count === 0) {
                $this->info('Creating new dummy user for app');
                $dummy = new User();
                $dummy->app_id = $app->id;
                $dummy->username = 'Gelöschter Benutzer';
                $dummy->email = 'dummy@keeunit.de';
                $dummy->password = Hash::make(Str::random());
                $dummy->tos_accepted = true;
                $dummy->active = true;
                $dummy->is_dummy = true;
                $dummy->is_admin = false;
                $dummy->is_bot = 0;
                $dummy->firstname = 'Gelöschter';
                $dummy->lastname = 'Benutzer';
                $dummy->save();
                $this->info('DummyUser created with ID #'.$dummy->id);
            }
        }
    }
}
