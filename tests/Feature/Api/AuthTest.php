<?php

namespace Tests\Feature\Api;

use App\Models\User;
use App\Services\AppProfileSettings;
use App\Services\AppSettings;
use App\Services\AuthEngine;
use Carbon\Carbon;
use Hash;
use Tests\TestCase;

class AuthTest extends TestCase
{
    protected User $user;
    const USER_PASSWORD = 'secret';

    public function setUp(): void
    {
        parent::setUp();
        $this->setAPIToken(null);

        $appSettings = new AppSettings($this->quizApp->id);
        $appSettings->setValue('has_login_limiations', 1);

        $appProfileSettings = new AppProfileSettings($this->quizApp->getDefaultAppProfile()->id);
        $appProfileSettings->setValue('max_concurrent_logins', 1);

        $this->user = User::factory()->active()->create([
            'app_id' => $this->quizApp->id,
            'password' => Hash::make(self::USER_PASSWORD),
        ]);
    }

    public function test_request_without_token_fails()
    {
        $response = $this->json('GET', '/api/v1/profile');
        $response->assertStatus(401);
    }

    public function test_request_with_valid_token_succeeds()
    {
        $token = $this->createToken();
        $this->setAPIToken($token);
        $response = $this->json('GET', '/api/v1/profile');
        $response->assertStatus(200);
    }

    public function test_request_login_token_is_valid()
    {
        $response = $this->loginRequest();
        $token = $response->json()['token'];
        $this->setAPIToken($token);
        $response = $this->json('GET', '/api/v1/profile');
        $response->assertStatus(200);
    }

    public function test_login_invalidates_old_tokens()
    {
        $response = $this->loginRequest();
        $firstToken = $response->json()['token'];
        $this->setAPIToken($firstToken);
        $response = $this->json('GET', '/api/v1/profile');
        $response->assertStatus(200);

        $response = $this->loginRequest();
        $response->assertJsonFragment([
            'deleted_tokens' => 1,
        ]);
        $secondToken = $response->json()['token'];
        $this->setAPIToken($secondToken);
        $response = $this->json('GET', '/api/v1/profile');
        $response->assertStatus(200);

        $this->setAPIToken($firstToken);
        $response = $this->json('GET', '/api/v1/profile');
        $response->assertStatus(401);
    }

    public function test_request_with_valid_token_but_inactive_user_fails()
    {
        $token = $this->createToken();
        $this->user->active = false;
        $this->user->save();

        $this->setAPIToken($token);
        $response = $this->json('GET', '/api/v1/profile');
        $response->assertStatus(401);
    }

    public function test_request_with_valid_token_but_deleted_user_fails()
    {
        $token = $this->createToken();
        $this->user->deleted_at = Carbon::now();
        $this->user->save();

        $this->setAPIToken($token);
        $response = $this->json('GET', '/api/v1/profile');
        $response->assertStatus(401);
    }

    public function test_logout_invalidates_tokens()
    {
        $token = $this->createToken();

        $this->setAPIToken($token);
        $this->json('POST', '/api/v1/logout');

        $response = $this->json('GET', '/api/v1/profile');
        $response->assertStatus(401);
    }

    private function loginRequest()
    {
        return $this->json('POST', '/api/v1/login', [
            'appId' => $this->quizApp->id,
            'email' => $this->user->email,
            'password' => self::USER_PASSWORD,
            'profileId' => $this->quizApp->getDefaultAppProfile()->id,
        ]);
    }

    private function createToken()
    {
        $token = auth('api')->login($this->user);
        (new AuthEngine)->updateAuthTokens($this->user, $token);
        return $token;
    }
}
