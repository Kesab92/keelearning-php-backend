<?php

namespace Tests\Feature\PublicApi\Tags;

use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Testing\Fluent\AssertableJson;
use Tests\TestCase;
use Tests\UseSpectator;

class CreateTagsTest extends TestCase
{
    use UseSpectator;

    protected User $apiUser;

    public function setUp(): void
    {
        parent::setUp();

        $this->apiUser = User::factory()
            ->active()
            ->create([
                'app_id' => $this->quizApp->id,
                'is_api_user' => true,
            ]);
    }

    /**
     * @return void
     */
    public function test_tag_can_be_created()
    {
        $tagData = [
            'label' => $this->faker->text(),
        ];

        $response = $this
            ->actingAs($this->apiUser)
            ->postJson('/api/public/v1/tags', $tagData);

        $response
            ->assertValidRequest()
            ->assertValidResponse(201);

        $response->assertJson(function (AssertableJson $json) use ($tagData) {
            $json
                ->whereType('id', 'integer')
                ->whereType('label', 'string');
        });

        $this->assertDatabaseHas('tags', ['id' => $response->json('id')]);
    }

    /**
     * @return void
     */
    public function test_tag_can_not_be_created_with_invalid_data()
    {
        $tagDataWithEmptyLabel = [
            'label' => '',
        ];
        $this->actingAs($this->apiUser)
            ->postJson('/api/public/v1/tags', $tagDataWithEmptyLabel)
            ->assertValidRequest()
            ->assertValidResponse(422);

        $tagDataWithTooLongLabel = [
            'label' => Str::random(256),
        ];
        $this->actingAs($this->apiUser)
            ->postJson('/api/public/v1/tags', $tagDataWithTooLongLabel)
            ->assertValidRequest()
            ->assertValidResponse(422);
    }

    /**
     * @return void
     */
    public function test_duplicated_tag_can_not_be_created()
    {
        $tagData = [
            'label' => $this->faker->text(),
        ];

        $this->actingAs($this->apiUser)
            ->postJson('/api/public/v1/tags', $tagData)
            ->assertValidRequest()
            ->assertValidResponse(201);

        $this->actingAs($this->apiUser)
            ->postJson('/api/public/v1/tags', $tagData)
            ->assertValidRequest()
            ->assertValidResponse(422);
    }
}
