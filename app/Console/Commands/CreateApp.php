<?php

namespace App\Console\Commands;

use App\Duplicators\Duplicator;
use App\Models\App;
use App\Models\AppProfile;
use App\Models\Page;
use App\Models\UserRole;
use App\Services\AppProfileSettings;
use App\Services\AppSettings;
use App\Services\MorphTypes;
use App\Traits\Duplicatable;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CreateApp extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:create';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Creates a new app';

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
     *  Creates a new app with all necessary additional data.
     */
    public function handle()
    {
        $id = null;
        while ($id === null || App::find($id)) {
            $id = $this->ask('Gimme the id of the new app');
        }
        $name = $this->ask('Gimme the name of the new app');
        $domain = $this->ask('Where is the app hosted?');
        $defaultLanguage = $this->ask('What is the default language of the app? (e.g. de, de_formal, en, es, ...)');
        $this->info('Creating app');

        DB::transaction(function () use ($defaultLanguage, $domain, $name, $id) {
            $app = new App();
            $app->id = $id;
            $app->name = $name;
            $app->app_hosted_at = $domain;
            $app->rounds_per_game = 3;
            $app->answers_per_question = 4;
            $app->questions_per_round = 4;
            $app->save();

            $appSettings = new AppSettings($app->id);
            $appSettings->setValue('defaultLanguage', $defaultLanguage);
            $appSettings->setValue('languages', json_encode([$defaultLanguage]));

            $this->info('Creating ToS page');
            $tosPage = new Page();
            $tosPage->app_id = $app->id;
            $tosPage->title = 'ToS';
            $tosPage->visible = 1;
            $tosPage->public = true;
            $tosPage->show_on_auth = true;
            $tosPage->content = 'Terms of Service';
            $tosPage->save();

            $appProfile = new AppProfile();
            $appProfile->name = 'Standard Profil';
            $appProfile->app_id = $app->id;
            $appProfile->is_default = true;
            $appProfile->save();
            $appProfileSettings = new AppProfileSettings($appProfile->id);
            $appProfileSettings->setValue('app_name', $name);
            $appProfileSettings->setValue('slug', App::SLUGS[$app->id]);
            $appProfileSettings->setValue('tos_id', $tosPage->id);

            $mainAdminRole = new UserRole();
            $mainAdminRole->app_id = $app->id;
            $mainAdminRole->name = 'Hauptadmin';
            $mainAdminRole->description = 'Ein Hauptadmin verfügt über alle Rechte und kann anderen Benutzern Rechte zuweisen und wieder entziehen.';
            $mainAdminRole->is_main_admin = true;
            $mainAdminRole->save();

            $this->info('Creating bots');
            \Artisan::call('app:bots');

            $this->info('Cloning initial data');
            $initialData = explode(',', env('NEW_APP_EXAMPLE_DATA'));
            $bar = $this->output->createProgressBar(count($initialData));
            $errors = [];
            $warnings = [];
            foreach ($initialData as $initialDataEntry) {
                [$type, $id] = explode(':', $initialDataEntry);
                $modelLookup = array_flip(MorphTypes::MAPPING);
                if (!array_key_exists($type, $modelLookup)) {
                    $errors[] = 'Type ' . $type . ' not found in MorphTypes';
                    $bar->advance();
                    continue;
                }
                $modelClass = $modelLookup[$type];
                $existingModel = $modelClass::find($id);
                if (!$existingModel) {
                    $errors[] = 'Could not find ' . $modelClass . ' #' . $id;
                    $bar->advance();
                    continue;
                }
                if (!in_array(Duplicatable::class, class_uses($existingModel))) {
                    $errors[] = 'Model ' . $modelClass . ' does not have a duplicator';
                    $bar->advance();
                    continue;
                }
                try {
                    $existingModel->duplicate($app->id);
                    Duplicator::getWarnings()->each(function ($warning) use (&$warnings) {
                        $warnings[] = $warning['type'] . ': ' . $warning['message'];
                    });
                } catch (Exception $e) {
                    $errors[] = 'Error cloning ' . $modelClass . '#' . $id . ': ' . $e->getMessage();
                }
                $bar->advance();
            }
            $bar->finish();
            $this->line('');

            foreach ($errors as $error) {
                $this->error($error);
            }

            foreach ($warnings as $warning) {
                $this->warn($warning);
            }
        });

        $this->info('Creating samba account');
        \Artisan::call('samba:createmissing');

        $this->info('All done 👍');
        $this->info('Remember to create a learninglocker account as well if it\'s needed.');
    }
}
