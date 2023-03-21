<?php

namespace Tests\Feature\PublicApi\Users;

use App\Models\App;
use App\Models\User;
use App\Services\AppProfileSettings;
use Arr;
use Illuminate\Testing\Fluent\AssertableJson;
use Tests\TestCase;
use Tests\UseSpectator;

class UpdateUserTest extends TestCase
{
    use UseSpectator;
    protected User $apiUser;
    protected User $testUser1;
    protected User $testUser2;

    public function setUp(): void
    {
        parent::setUp();

        $appProfileSettings = new AppProfileSettings($this->quizApp->getDefaultAppProfile()->id);
        $appProfileSettings->setValue('signup_show_email', 1);
        $appProfileSettings->setValue('signup_show_email_mandatory', 'mandatory');

        $this->apiUser = User::factory()
            ->active()
            ->create([
                'app_id' => $this->quizApp->id,
                'is_api_user' => true,
            ]);
        $this->testUser1 = User::factory()
            ->active()
            ->create([
                'app_id' => $this->quizApp->id,
                'language' => $this->quizApp->getLanguage(),
            ]);
        $this->testUser2 = User::factory()
            ->active()
            ->create([
                'app_id' => $this->quizApp->id,
                'language' => $this->quizApp->getLanguage(),
            ]);
    }

    public function test_user_can_be_updated()
    {
        $userData = [
            'username' => $this->faker->userName,
            'firstname' => $this->faker->firstName,
            'lastname' => $this->faker->lastName,
            'email' => $this->faker->email,
            'password' => 'thisISaV3ryV4lidP4$$word',
            'language' => $this->quizApp->getLanguage(),
            'active' => $this->faker->boolean,
            'tags' => [],
            'meta' => ['company' => $this->faker->company],
        ];

        $this
            ->actingAs($this->apiUser)
            ->putJson('/api/public/v1/users/' . $this->testUser1->id, $userData)
            ->assertValidRequest()
            ->assertValidResponse(200)
            ->assertJson(function (AssertableJson $json) use ($userData) {
                $json
                    ->whereAll(Arr::except($userData, [
                        'tags',
                        'password',
                    ]))
                    ->whereType('id', 'integer')
                    ->whereType('tags', 'array')
                    ->whereType('meta', 'array')
                    ->where('created_at', $this->isDate());
            });
    }

    public function test_username_and_email_are_trimmed()
    {
        $username = $this->faker->userName;
        $email = $this->faker->email;
        $userData = [
            'email' => ' '.$email.' ',
            'username' => ' '.$username.' ',
        ];

        $this
            ->actingAs($this->apiUser)
            ->putJson('/api/public/v1/users/' . $this->testUser1->id, $userData)
            ->assertValidRequest()
            ->assertValidResponse(200)
            ->assertJson([
                'email' => $email,
                'username' => $username,
            ]);
        $this->assertDatabaseHas('users', [
            'id' => $this->testUser1->id,
            'username' => $username,
            'email' => $email,
        ]);
    }

    public function test_user_can_be_updated_with_existing_data()
    {
        $userData = [
            'username' => $this->testUser1->username,
            'email' => $this->testUser1->email,
            'firstname' => $this->testUser1->firstname,
            'lastname' => $this->testUser1->lastname,
            'language' => $this->testUser1->language,
            'active' => $this->testUser1->active,
            'meta' => $this->testUser1->getMeta(),
        ];

        $response = $this
            ->actingAs($this->apiUser)
            ->putJson('/api/public/v1/users/' . $this->testUser1->id, $userData)
            ->assertValidRequest()
            ->assertValidResponse(200)
            ->assertJson(function (AssertableJson $json) use ($userData) {
                $json
                    ->whereAll($userData)
                    ->whereType('id', 'integer')
                    ->whereType('tags', 'array')
                    ->whereType('meta', 'array')
                    ->where('created_at', $this->isDate());
            });
    }

    public function test_user_can_not_be_updated_with_invalid_data()
    {
        $this->actingAs($this->apiUser)
            ->putJson('/api/public/v1/users/' . $this->testUser1->id, ['username' => ''])
            ->assertValidRequest()
            ->assertValidResponse(422);

        $this->actingAs($this->apiUser)
            ->putJson('/api/public/v1/users/' . $this->testUser1->id, ['password' => 'weakpassword'])
            ->assertValidRequest()
            ->assertValidResponse(422);

        $this->actingAs($this->apiUser)
            ->putJson('/api/public/v1/users/' . $this->testUser1->id, ['email' => $this->faker->userName])
            ->assertValidRequest()
            ->assertValidResponse(422);

        $this->actingAs($this->apiUser)
            ->putJson('/api/public/v1/users/' . $this->testUser1->id, ['language' => 'klingon'])
            ->assertValidRequest()
            ->assertValidResponse(422);

        $this->actingAs($this->apiUser)
            ->putJson('/api/public/v1/users/' . $this->testUser1->id, ['meta' => ['favorite_movie' => 'Joker (2019)']])
            ->assertValidRequest()
            ->assertValidResponse(422);

        $this->actingAs($this->apiUser)
            ->putJson('/api/public/v1/users/' . $this->testUser1->id, ['meta' => ['company' => [$this->faker->company]]])
            ->assertValidRequest()
            ->assertValidResponse(422);

        $this->actingAs($this->apiUser)
            ->putJson('/api/public/v1/users/' . $this->testUser1->id, ['tags' => [0, 9999]])
            ->assertValidRequest()
            ->assertValidResponse(422);
    }

    public function test_user_can_not_remove_email()
    {
        $this
            ->actingAs($this->apiUser)
            ->putJson('/api/public/v1/users/' . $this->testUser1->id, ['email' => ''])
            ->assertValidRequest()
            ->assertValidResponse(422);
    }

    public function test_user_can_not_be_updated_with_duplicate_email()
    {
        $this
            ->actingAs($this->apiUser)
            ->putJson('/api/public/v1/users/' . $this->testUser1->id, ['email' => $this->testUser2->email])
            ->assertValidRequest()
            ->assertValidResponse(422);
    }

    public function test_user_can_be_updated_with_duplicate_username()
    {
        $this
            ->actingAs($this->apiUser)
            ->putJson('/api/public/v1/users/' . $this->testUser1->id, ['username' => $this->testUser2->username])
            ->assertValidRequest()
            ->assertValidResponse(200)
            ->assertJson(['username' => $this->testUser2->username]);
    }

    public function test_user_in_mailless_app_can_remove_email()
    {
        $appProfileSettings = new AppProfileSettings($this->quizApp->getDefaultAppProfile()->id);
        $appProfileSettings->setValue('signup_show_email', 0);
        $appProfileSettings->setValue('signup_show_email_mandatory', 'optional');

        $this
            ->actingAs($this->apiUser)
            ->putJson('/api/public/v1/users/' . $this->testUser1->id, ['email' => ''])
            ->assertValidRequest()
            ->assertValidResponse(200)
            ->assertJson(['email' => '']);
    }

    public function test_user_in_mailless_app_can_not_be_updated_with_duplicate_email()
    {
        $appProfileSettings = new AppProfileSettings($this->quizApp->getDefaultAppProfile()->id);
        $appProfileSettings->setValue('signup_show_email', 0);
        $appProfileSettings->setValue('signup_show_email_mandatory', 'optional');

        $this
            ->actingAs($this->apiUser)
            ->putJson('/api/public/v1/users/' . $this->testUser1->id, ['email' => $this->testUser2->email])
            ->assertValidRequest()
            ->assertValidResponse(422);
    }

    public function test_user_in_mailless_app_can_not_be_updated_with_duplicate_username()
    {
        $appProfileSettings = new AppProfileSettings($this->quizApp->getDefaultAppProfile()->id);
        $appProfileSettings->setValue('signup_show_email', 0);
        $appProfileSettings->setValue('signup_show_email_mandatory', 'optional');

        $this
            ->actingAs($this->apiUser)
            ->putJson('/api/public/v1/users/' . $this->testUser1->id, ['username' => $this->testUser2->username])
            ->assertValidRequest()
            ->assertValidResponse(422);
    }

    public function test_user_can_not_be_updated_with_duplicate_unique_meta()
    {
        $quizApp = $this->createQuizApp(App::ID_BLUME2000);

        $apiUser = User::factory()
            ->active()
            ->create([
                'app_id' => $quizApp->id,
                'is_api_user' => true,
            ]);


        $testUser1 = User::factory()
            ->active()
            ->create([
                'app_id' => $quizApp->id,
                'language' => $quizApp->getLanguage(),
            ]);
        $testUser2 = User::factory()
            ->active()
            ->create([
                'app_id' => $quizApp->id,
                'language' => $quizApp->getLanguage(),
            ]);
        $testUser2->setMeta('login', $this->faker->userName);

        $this
            ->actingAs($apiUser)
            ->putJson('/api/public/v1/users/' . $testUser1->id, [
                'meta' => [
                    'login' => $testUser2->getMeta('login'),
                ],
            ])
            ->assertValidRequest()
            ->assertValidResponse(422);
    }
}
