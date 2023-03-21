<?php

namespace Tests\Feature\PublicApi\Users;

use App\Models\App;
use App\Models\Tag;
use App\Models\User;
use App\Services\AppProfileSettings;
use Arr;
use Illuminate\Testing\Fluent\AssertableJson;
use Tests\TestCase;
use Tests\UseSpectator;

class CreateUserTest extends TestCase
{
    use UseSpectator;
    protected User $apiUser;

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
    }

    public function test_user_can_be_created()
    {
        $tag = Tag::factory()
            ->create(['app_id' => $this->quizAppId]);
        $userData = [
            'app_id' => $this->quizAppId,
            'active' => $this->faker->boolean,
            'email' => $this->faker->email,
            'firstname' => $this->faker->firstName,
            'language' => $this->quizApp->getLanguage(),
            'lastname' => $this->faker->lastName,
            'password' => 'thisISaV3ryV4lidP4$$word',
            'tags' => [$tag->id],
            'username' => $this->faker->userName,
            'meta' => ['company' => $this->faker->company],
        ];

        $response = $this
            ->actingAs($this->apiUser)
            ->postJson('/api/public/v1/users', $userData);

        $response
            ->assertValidRequest()
            ->assertValidResponse(201);

        $response->assertJson(function (AssertableJson $json) use ($tag, $userData) {
            $json
                ->has('tags', 1, function (AssertableJson $json) use ($tag) {
                    $json
                        ->whereAll([
                            'id' => $tag->id,
                            'label' => $tag->label,
                        ]);
                })
                ->whereAll(Arr::except($userData, [
                    'app_id',
                    'password',
                    'tags',
                    'meta',
                ]))
                ->whereType('id', 'integer')
                ->whereType('meta', 'array')
                ->whereType('tags', 'array')
                ->where('created_at', $this->isDate());
        });

        $this->assertDatabaseHas('users', ['id' => $response->json('id')]);
    }
    public function test_username_and_email_are_trimmed()
    {
        $username = $this->faker->userName;
        $email = $this->faker->email;
        $userData = [
            'email' => ' '.$email.' ',
            'password' => 'thisISaV3ryV4lidP4$$word',
            'username' => ' '.$username.' ',
        ];

        $response = $this
            ->actingAs($this->apiUser)
            ->postJson('/api/public/v1/users', $userData);

        $response
            ->assertValidRequest()
            ->assertValidResponse(201);

        $response->assertJson([
            'username' => $username,
            'email' => $email,
        ]);

        $this->assertDatabaseHas('users', [
            'id' => $response->json('id'),
            'username' => $username,
            'email' => $email,
        ]);
    }

    public function test_user_can_be_created_with_unsafe_password()
    {
        // Yes, this seems like a weird test, but for users created via the API we do not enforce
        // any password guidelines and let the customer handle that responsibility.
        $userDataWithWeakPassword = [
            'username' => $this->faker->userName,
            'email' => $this->faker->email,
            'password' => 'weakpassword',
            'language' => $this->quizApp->getLanguage(),
            'active' => $this->faker->boolean,
        ];
        $response = $this->actingAs($this->apiUser)
            ->postJson('/api/public/v1/users', $userDataWithWeakPassword)
            ->assertValidRequest()
            ->assertValidResponse(201);
        $response->assertJson(function (AssertableJson $json) use ($userDataWithWeakPassword) {
            $json
                ->whereAll(Arr::except($userDataWithWeakPassword, [
                    'app_id',
                    'tags',
                    'password',
                ]))
                ->whereType('id', 'integer')
                ->whereType('tags', 'array')
                ->whereType('meta', 'array')
                ->whereType('firstname', 'string')
                ->whereType('lastname', 'string')
                ->where('created_at', $this->isDate());
        });

        $this->assertDatabaseHas('users', ['id' => $response->json('id')]);
    }

    public function test_user_can_not_be_created_with_invalid_data()
    {
        $userDataWithEmptyUsername = [
            'username' => '',
            'email' => $this->faker->email,
            'password' => 'thisISaV3ryV4lidP4$$word',
            'language' => $this->quizApp->getLanguage(),
            'active' => $this->faker->boolean,
        ];
        $this->actingAs($this->apiUser)
            ->postJson('/api/public/v1/users', $userDataWithEmptyUsername)
            ->assertValidRequest()
            ->assertValidResponse(422);

        $userDataWithInvalidMail = [
            'username' => $this->faker->userName,
            'email' => $this->faker->userName,
            'password' => 'thisISaV3ryV4lidP4$$word',
            'language' => $this->quizApp->getLanguage(),
            'active' => $this->faker->boolean,
        ];
        $this->actingAs($this->apiUser)
            ->postJson('/api/public/v1/users', $userDataWithInvalidMail)
            ->assertValidRequest()
            ->assertValidResponse(422);

        $userDataWithInvalidLanguage = [
            'username' => $this->faker->userName,
            'email' => $this->faker->email,
            'password' => 'thisISaV3ryV4lidP4$$word',
            'language' => 'klingon',
            'active' => $this->faker->boolean,
        ];
        $this->actingAs($this->apiUser)
            ->postJson('/api/public/v1/users', $userDataWithInvalidLanguage)
            ->assertValidRequest()
            ->assertValidResponse(422);

        $userDataWithInvalidMetadata = [
            'username' => $this->faker->userName,
            'email' => $this->faker->email,
            'password' => 'thisISaV3ryV4lidP4$$word',
            'language' => $this->quizApp->getLanguage(),
            'active' => $this->faker->boolean,
            'meta' => [
                'favorite_movie' => 'Joker (2019)',
            ],
        ];
        $this->actingAs($this->apiUser)
            ->postJson('/api/public/v1/users', $userDataWithInvalidMetadata)
            ->assertValidRequest()
            ->assertValidResponse(422);

        $userDataWithInvalidMetadata = [
            'app_id' => $this->quizAppId,
            'username' => $this->faker->userName,
            'email' => $this->faker->email,
            'password' => 'thisISaV3ryV4lidP4$$word',
            'language' => $this->quizApp->getLanguage(),
            'active' => $this->faker->boolean,
            'meta' => [
                'company' => [$this->faker->company],
            ],
        ];
        $this->actingAs($this->apiUser)
            ->postJson('/api/public/v1/users', $userDataWithInvalidMetadata)
            ->assertValidRequest()
            ->assertValidResponse(422);

        $userDataWithInvalidTags = [
            'username' => $this->faker->userName,
            'email' => $this->faker->email,
            'password' => 'thisISaV3ryV4lidP4$$word',
            'language' => $this->quizApp->getLanguage(),
            'active' => $this->faker->boolean,
            'tags' => [0, 9999],
        ];
        $this->actingAs($this->apiUser)
            ->postJson('/api/public/v1/users', $userDataWithInvalidTags)
            ->assertValidRequest()
            ->assertValidResponse(422);
    }

    public function test_user_can_not_be_created_without_email()
    {
        $userData = [
            'username' => $this->faker->userName,
            'firstname' => $this->faker->firstName,
            'lastname' => $this->faker->lastName,
            'email' => '',
            'password' => 'thisISaV3ryV4lidP4$$word',
            'language' => $this->quizApp->getLanguage(),
            'active' => $this->faker->boolean,
            'tags' => [],
        ];

        $response = $this
            ->actingAs($this->apiUser)
            ->postJson('/api/public/v1/users', $userData)
            ->assertValidRequest()
            ->assertValidResponse(422);

        $userData = [
            'username' => $this->faker->userName,
            'firstname' => $this->faker->firstName,
            'lastname' => $this->faker->lastName,
            'password' => 'thisISaV3ryV4lidP4$$word',
            'language' => $this->quizApp->getLanguage(),
            'active' => $this->faker->boolean,
            'tags' => [],
        ];

        $response = $this
            ->actingAs($this->apiUser)
            ->postJson('/api/public/v1/users', $userData)
            ->assertValidRequest()
            ->assertValidResponse(422);
    }

    public function test_user_can_not_be_created_with_duplicate_email()
    {
        $duplicateEmail = $this->faker->email;
        $userData = [
            'username' => $this->faker->userName,
            'firstname' => $this->faker->firstName,
            'lastname' => $this->faker->lastName,
            'email' => $duplicateEmail,
            'password' => 'thisISaV3ryV4lidP4$$word',
            'language' => $this->quizApp->getLanguage(),
            'active' => $this->faker->boolean,
            'tags' => [],
        ];

        $response = $this
            ->actingAs($this->apiUser)
            ->postJson('/api/public/v1/users', $userData)
            ->assertValidRequest()
            ->assertValidResponse(201);

        $userData = [
            'username' => $this->faker->userName,
            'firstname' => $this->faker->firstName,
            'lastname' => $this->faker->lastName,
            'email' => $duplicateEmail,
            'password' => 'thisISaV3ryV4lidP4$$word',
            'language' => $this->quizApp->getLanguage(),
            'active' => $this->faker->boolean,
            'tags' => [],
        ];

        $response = $this
            ->actingAs($this->apiUser)
            ->postJson('/api/public/v1/users', $userData)
            ->assertValidRequest()
            ->assertValidResponse(422);
    }

    public function test_user_can_be_created_with_duplicate_username()
    {
        $duplicateUsername = $this->faker->userName;
        $userData = [
            'username' => $duplicateUsername,
            'firstname' => $this->faker->firstName,
            'lastname' => $this->faker->lastName,
            'email' => $this->faker->email,
            'password' => 'thisISaV3ryV4lidP4$$word',
            'language' => $this->quizApp->getLanguage(),
            'active' => $this->faker->boolean,
            'tags' => [],
        ];

        $response = $this
            ->actingAs($this->apiUser)
            ->postJson('/api/public/v1/users', $userData)
            ->assertValidRequest()
            ->assertValidResponse(201);

        $userData = [
            'username' => $duplicateUsername,
            'firstname' => $this->faker->firstName,
            'lastname' => $this->faker->lastName,
            'email' => $this->faker->email,
            'password' => 'thisISaV3ryV4lidP4$$word',
            'language' => $this->quizApp->getLanguage(),
            'active' => $this->faker->boolean,
            'tags' => [],
        ];

        $response = $this
            ->actingAs($this->apiUser)
            ->postJson('/api/public/v1/users', $userData)
            ->assertValidRequest()
            ->assertValidResponse(201);
    }

    public function test_user_in_mailless_app_can_be_created_without_email()
    {
        $appProfileSettings = new AppProfileSettings($this->quizApp->getDefaultAppProfile()->id);
        $appProfileSettings->setValue('signup_show_email', 0);
        $appProfileSettings->setValue('signup_show_email_mandatory', 'optional');

        $userData = [
            'username' => $this->faker->userName,
            'firstname' => $this->faker->firstName,
            'lastname' => $this->faker->lastName,
            'email' => '',
            'password' => 'thisISaV3ryV4lidP4$$word',
            'language' => $this->quizApp->getLanguage(),
            'active' => $this->faker->boolean,
            'tags' => [],
        ];

        $response = $this
            ->actingAs($this->apiUser)
            ->postJson('/api/public/v1/users', $userData)
            ->assertValidRequest()
            ->assertValidResponse(201);

        $userData = [
            'username' => $this->faker->userName,
            'firstname' => $this->faker->firstName,
            'lastname' => $this->faker->lastName,
            'password' => 'thisISaV3ryV4lidP4$$word',
            'language' => $this->quizApp->getLanguage(),
            'active' => $this->faker->boolean,
            'tags' => [],
        ];

        $response = $this
            ->actingAs($this->apiUser)
            ->postJson('/api/public/v1/users', $userData)
            ->assertValidRequest()
            ->assertValidResponse(201);
    }

    public function test_user_in_mailless_app_can_not_be_created_with_duplicate_email()
    {
        $appProfileSettings = new AppProfileSettings($this->quizApp->getDefaultAppProfile()->id);
        $appProfileSettings->setValue('signup_show_email', 0);
        $appProfileSettings->setValue('signup_show_email_mandatory', 'optional');

        $duplicateEmail = $this->faker->email;
        $userData = [
            'username' => $this->faker->userName,
            'firstname' => $this->faker->firstName,
            'lastname' => $this->faker->lastName,
            'email' => $duplicateEmail,
            'password' => 'thisISaV3ryV4lidP4$$word',
            'language' => $this->quizApp->getLanguage(),
            'active' => $this->faker->boolean,
            'tags' => [],
        ];

        $response = $this
            ->actingAs($this->apiUser)
            ->postJson('/api/public/v1/users', $userData)
            ->assertValidRequest()
            ->assertValidResponse(201);

        $userData = [
            'username' => $this->faker->userName,
            'firstname' => $this->faker->firstName,
            'lastname' => $this->faker->lastName,
            'email' => $duplicateEmail,
            'password' => 'thisISaV3ryV4lidP4$$word',
            'language' => $this->quizApp->getLanguage(),
            'active' => $this->faker->boolean,
            'tags' => [],
        ];

        $response = $this
            ->actingAs($this->apiUser)
            ->postJson('/api/public/v1/users', $userData)
            ->assertValidRequest()
            ->assertValidResponse(422);
    }

    public function test_user_in_mailless_app_can_not_be_created_with_duplicate_username()
    {
        $appProfileSettings = new AppProfileSettings($this->quizApp->getDefaultAppProfile()->id);
        $appProfileSettings->setValue('signup_show_email', 0);
        $appProfileSettings->setValue('signup_show_email_mandatory', 'optional');

        $duplicateUsername = $this->faker->userName;
        $userData = [
            'username' => $duplicateUsername,
            'firstname' => $this->faker->firstName,
            'lastname' => $this->faker->lastName,
            'email' => $this->faker->email,
            'password' => 'thisISaV3ryV4lidP4$$word',
            'language' => $this->quizApp->getLanguage(),
            'active' => true,
            'tags' => [],
        ];

        $response = $this
            ->actingAs($this->apiUser)
            ->postJson('/api/public/v1/users', $userData)
            ->assertValidRequest()
            ->assertValidResponse(201);

        $userData = [
            'username' => $duplicateUsername,
            'firstname' => $this->faker->firstName,
            'lastname' => $this->faker->lastName,
            'email' => $this->faker->email,
            'password' => 'thisISaV3ryV4lidP4$$word',
            'language' => $this->quizApp->getLanguage(),
            'active' => true,
            'tags' => [],
        ];

        $response = $this
            ->actingAs($this->apiUser)
            ->postJson('/api/public/v1/users', $userData);
        $response->assertValidRequest()
            ->assertValidResponse(422);
    }

    public function test_user_can_not_be_created_with_duplicate_unique_meta()
    {
        $quizApp = $this->createQuizApp(App::ID_BLUME2000);

        $apiUser = User::factory()
            ->active()
            ->create([
                'app_id' => $quizApp->id,
                'is_api_user' => true,
            ]);

        $duplicateMetaData = $this->faker->userName;
        $userData = [
            'app_id' => $apiUser->app_id,
            'username' => $this->faker->userName,
            'firstname' => $this->faker->firstName,
            'lastname' => $this->faker->lastName,
            'email' => $this->faker->email,
            'password' => 'thisISaV3ryV4lidP4$$word',
            'language' => $quizApp->getLanguage(),
            'active' => true,
            'tags' => [],
            'meta' => [
                'login' => $duplicateMetaData,
            ],
        ];

        $response = $this
            ->actingAs($apiUser)
            ->postJson('/api/public/v1/users', $userData)
            ->assertValidRequest()
            ->assertValidResponse(201);

        $userData = [
            'app_id' => $apiUser->app_id,
            'username' => $this->faker->userName,
            'firstname' => $this->faker->firstName,
            'lastname' => $this->faker->lastName,
            'email' => $this->faker->email,
            'password' => 'thisISaV3ryV4lidP4$$word',
            'language' => $quizApp->getLanguage(),
            'active' => true,
            'tags' => [],
            'meta' => [
                'login' => $duplicateMetaData,
            ],
        ];

        $response = $this
            ->actingAs($apiUser)
            ->postJson('/api/public/v1/users', $userData)
            ->assertValidRequest()
            ->assertValidResponse(422);
    }
}
