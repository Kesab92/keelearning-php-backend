<?php

namespace Tests\Feature\PublicApi;

use App\Models\User;
use Tests\TestCase;
use Tests\UseSpectator;

class ThrottleTest extends TestCase
{
    use UseSpectator;

    /**
     * @return void
     */
    public function test_requests_get_throttled_after_threshold()
    {
        // FIXME: this will break because of the prior public API calls already using up part of the allowance
        User::factory()
            ->count(5)
            ->create(['app_id' => $this->quizApp->id]);
        $user = User::first();

        for ($i = 0; $i < 100; $i++) {
            $response = $this
                ->actingAs($user)
                ->getJson('/api/public/v1/users');
            // Check that the first 100 requests are successful
            $response
                ->assertValidResponse(200);
        }

        // Check that the 51st request gets denied
        $response = $this
            ->actingAs($user)
            ->getJson('/api/public/v1/users');

        $response
            ->assertValidResponse(429);
    }
}
