<?php

namespace Tests\Feature\PublicApi;

use App\Models\App;
use App\Models\User;
use Tests\TestCase;
use Tests\UseSpectator;

class AuthTest extends TestCase
{
    use UseSpectator;

    /**
     * @return void
     */
    public function test_api_requests_without_auth_are_rejected()
    {
        User::factory()
            ->count(5)
            ->create(['app_id' => $this->quizApp->id]);

        $response = $this->getJson('/api/public/v1/users');

        $response
            ->assertValidRequest()
            ->assertValidResponse(401);
    }

    /**
     * @return void
     */
    public function test_api_requests_with_auth_are_successful()
    {
        User::factory()
            ->count(5)
            ->create(['app_id' => $this->quizApp->id]);

        $user = User::first();
        $token = $user->createToken($user->id);

        $response = $this
            ->withHeader('Authorization', 'Bearer ' . $token->plainTextToken)
            ->getJson('/api/public/v1/users');

        $response
            ->assertValidRequest()
            ->assertValidResponse(200);
    }
}
