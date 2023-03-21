<?php

namespace Database\Factories;

use App\Models\App;
use App\Models\AppProfile;
use App\Models\Page;
use App\Models\User;
use App\Models\UserRole;
use App\Services\AppProfileSettings;
use App\Services\AppSettings;
use Faker\Factory as FakerFactory;
use Illuminate\Database\Eloquent\Factories\Factory;

class AppFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = App::class;

    /**
     * Configure the model factory.
     *
     * @return $this
     */
    public function configure()
    {
        return $this->afterCreating(function (App $app) {

            $appSettings = new AppSettings($app->id);
            $appSettings->setValue('defaultLanguage', 'de');
            $appSettings->setValue('languages', json_encode(['de']));

            $tosPage = Page::factory()->create([
                'app_id' => $app->id,
                'title' => 'ToS',
                'visible' => true,
                'public' => true,
                'show_on_auth' => true,
            ]);
            $appProfile = AppProfile::factory()->default()->create([
                'app_id' => $app->id,
            ]);
            $appProfileSettings = new AppProfileSettings($appProfile->id);
            $appProfileSettings->setValue('slug', App::SLUGS[$app->id]);
            $appProfileSettings->setValue('tos_id', $tosPage->id);
            User::factory()
                ->dummy()
                ->create(['app_id' => $app->id]);
            $mainAdminRole = new UserRole();
            $mainAdminRole->app_id = $app->id;
            $mainAdminRole->name = 'Hauptadmin';
            $mainAdminRole->description = 'Ein Hauptadmin verfügt über alle Rechte und kann anderen Benutzern Rechte zuweisen und wieder entziehen.';
            $mainAdminRole->is_main_admin = true;
            $mainAdminRole->save();

            $regularAdminRole = new UserRole();
            $regularAdminRole->app_id = $app->id;
            $regularAdminRole->name = 'Regular admin without any rights';
            $regularAdminRole->description = 'Regular admin without any rights.';
            $regularAdminRole->is_main_admin = false;
            $regularAdminRole->save();
        });
    }

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'answers_per_question' => 4,
            'app_hosted_at' => 'https://' . $this->faker->domainName(),
            'name' => $this->faker->company() . ' App',
            'questions_per_round' => 4,
            'rounds_per_game' => 3,
        ];
    }

    protected function withFaker()
    {
        return FakerFactory::create(config('app.faker_locale'));
    }
}
