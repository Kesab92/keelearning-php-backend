<?php

namespace App\Console\Commands;

use App\Models\App;
use App\Models\AppProfile;
use App\Models\AppProfileSetting;
use App\Services\AppSettings;
use App\Services\ImageUploader;
use App\Services\StatsEngine;
use Cache;
use Illuminate\Console\Command;
use Illuminate\Support\Str;
use Storage;

class MigrateSettingsToProfiles extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'migrate:settingstoprofiles';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Creates app profiles and migrates old settings to new profiles';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $apps = App::all();
        $this->info('Start migrating settings');
        $bar = $this->output->createProgressBar($apps->count());
        \DB::transaction(function () use ($apps, $bar) {
            foreach ($apps as $app) {
                $appProfile = new AppProfile();
                $appProfile->app_id = $app->id;
                $appProfile->name = 'Standard Profil';
                $appProfile->save();
                $appSettings = new AppSettings($app->id);
                $this->setProfileSetting($appProfile, 'app_name', $app->name);
                $this->setProfileSetting($appProfile, 'app_name_short', $app->name);
                $this->setProfileSetting($appProfile, 'subdomain', $this->getSubdomain($app));
                $this->setProfileSetting($appProfile, 'quiz_users_choose_categories', $appSettings->getValue('users_choose_categories'));
                $this->setProfileSetting($appProfile, 'bot_game_mails', $appSettings->getValue('bot_game_mails'));
                $this->setProfileSetting($appProfile, 'hide_emails_frontend', $appSettings->getValue('hide_emails_frontend'));
                $this->setProfileSetting($appProfile, 'quiz_no_weekend_grace_period', $appSettings->getValue('no_weekend_grace_period'));
                $this->setProfileSetting($appProfile, 'hide_given_test_answers', $appSettings->getValue('hide_given_test_answers'));
                $this->setProfileSetting($appProfile, 'quiz_hide_player_statistics', $appSettings->getValue('hide_general_quiz_statistics'));
                $this->setProfileSetting($appProfile, 'quiz_round_answer_time', $appSettings->getValue('round_answer_time'));
                $this->setProfileSetting($appProfile, 'quiz_round_initial_answer_time', $appSettings->getValue('round_initial_answer_time'));
                $this->setProfileSetting($appProfile, 'quiz_default_answer_time', $appSettings->getValue('default_answer_time'));

                if ($app->needsNameForSignup()) {
                    $this->setProfileSetting($appProfile, 'signup_show_firstname', 1);
                    $this->setProfileSetting($appProfile, 'signup_show_firstname_mandatory', 'mandatory');
                    $this->setProfileSetting($appProfile, 'signup_show_lastname', 1);
                    $this->setProfileSetting($appProfile, 'signup_show_lastname_mandatory', 'mandatory');
                }
                if ($app->allowMaillessSignup()) {
                    $this->setProfileSetting($appProfile, 'signup_show_email', 1);
                    $this->setProfileSetting($appProfile, 'signup_show_email_mandatory', '');
                } else {
                    $this->setProfileSetting($appProfile, 'signup_show_email', 1);
                    $this->setProfileSetting($appProfile, 'signup_show_email_mandatory', 'mandatory');
                }
                $this->setProfileSetting($appProfile, 'app_icon', $this->getAppIcon($app));

                $this->migrateToCustomerModules($appSettings);

                $bar->advance();
            }
        });
        $bar->finish();
    }

    private function migrateToCustomerModules(AppSettings $appSettings)
    {
        $mappings = [
            'hide_news' => 'module_news',
            'hide_questions' => 'module_questions',
            'hide_index_cards' => 'module_index_cards',
            'hide_competitions' => 'module_competitions',
            'hide_tests' => 'module_tests',
            'hide_learningmaterials' => 'module_learningmaterials',
            'hide_vouchers' => 'module_vouchers',
            'hide_webinars' => 'module_webinars',
        ];

        foreach ($mappings as $original => $module) {
            $original = $appSettings->getValue($original);
            if (! $original) {
                $value = 1;
            } else {
                $value = 0;
            }
            $appSettings->setValue($module, $value);
        }

        $appSettings->setValue('module_quiz', 1);
        $appSettings->setValue('module_bots', 1);
        $appSettings->setValue('module_suggested_questions', 1);
    }

    private function setProfileSetting(AppProfile $appProfile, $key, $value)
    {
        $appProfileSetting = new AppProfileSetting();
        $appProfileSetting->app_profile_id = $appProfile->id;
        $appProfileSetting->key = $key;
        if ($value === null) {
            $value = '';
        }
        $appProfileSetting->value = $value;
        $appProfileSetting->save();
    }

    private function getSubdomain(App $app)
    {
        $domain = str_replace('https://', '', $app->app_hosted_at);
        $expl = explode('.', $domain);
        if ($expl[1] === 'keelearning') {
            return $expl[0];
        }

        return '';
    }

    private function getAppIcon(App $app)
    {
        try {
            $data = file_get_contents($app->app_hosted_at.'/static/images/icons/icon-512x512.png');
            $imagePath = 'uploads/'.Str::slug('app_icon').'/'.Str::random(30).'.png';
            Storage::put($imagePath, $data);
            $imagePath = formatAssetURL($imagePath, '3.0.0');

            return $imagePath;
        } catch (\Exception $e) {
            return '';
        }
    }
}
