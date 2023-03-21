<?php

namespace Tests\Feature\PublicApi\Users;

use App\Models\User;
use Tests\TestCase;
use Tests\UseSpectator;

class DeleteUserTest extends TestCase
{
    use UseSpectator;

    public function test_user_can_be_deleted()
    {
        User::factory()
            ->active()
            ->count(2)
            ->create(['app_id' => $this->quizApp->id]);

        $apiUser = User::first();
        $userToDelete = User::where('id', '!=', $apiUser->id)->first();

        $response = $this
            ->actingAs($apiUser)
            ->delete('/api/public/v1/users/' . $userToDelete->id);

        $response
            ->assertValidRequest()
            ->assertValidResponse(204);

        $this->assertDatabaseMissing('users', ['id' => $userToDelete->id]);
    }
}
