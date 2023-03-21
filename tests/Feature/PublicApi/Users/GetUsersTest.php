<?php

namespace Tests\Feature\PublicApi\Users;

use App\Models\App;
use App\Models\User;
use Illuminate\Testing\Fluent\AssertableJson;
use Tests\TestCase;
use Tests\UseSpectator;

class GetUsersTest extends TestCase
{
    use UseSpectator;

    /**
     * @return void
     */
    public function test_user_list_returns_correct_results()
    {
        User::factory()
            ->active()
            ->count(5)
            ->create(['app_id' => $this->quizApp->id]);

        $response = $this
            ->actingAs(User::first())
            ->getJson('/api/public/v1/users');

        $response
            ->assertValidRequest()
            ->assertValidResponse(200);

        $response->assertJson(function (AssertableJson $json) {
            $json->has(5)->first(function (AssertableJson $json) {
                $json
                    ->whereAllType([
                        'id' => 'integer',
                        'username' => 'string',
                        'firstname' => 'string|null',
                        'lastname' => 'string|null',
                        'email' => 'string',
                        'language' => 'string|null',
                        'active' => 'boolean',
                        'tags' => 'array',
                        'created_at' => 'string',
                        'meta' => 'array',
                    ])
                    ->where('created_at', $this->isDate());
            });
        });
    }
}
