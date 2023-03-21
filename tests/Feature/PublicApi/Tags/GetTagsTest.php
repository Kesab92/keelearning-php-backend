<?php

namespace Tests\Feature\PublicApi\Tags;

use App\Models\Tag;
use App\Models\User;
use Illuminate\Testing\Fluent\AssertableJson;
use Tests\TestCase;
use Tests\UseSpectator;

class GetTagsTest extends TestCase
{
    use UseSpectator;

    /**
     * @return void
     */
    public function test_tag_list_returns_correct_results()
    {
        User::factory()
            ->create(['app_id' => $this->quizApp->id]);
        Tag::factory()
            ->count(5)
            ->create(['app_id' => $this->quizApp->id]);
        Tag::factory()
            ->count(5)
            ->deleted()
            ->create(['app_id' => $this->quizApp->id]);

        $response = $this
            ->actingAs(User::first())
            ->getJson('/api/public/v1/tags');

        $response
            ->assertValidRequest()
            ->assertValidResponse(200);

        $response->assertJson(function (AssertableJson $json) {
            $json->has(5)->first(function (AssertableJson $json) {
                $json->whereAllType([
                    'id' => 'integer',
                    'label' => 'string',
                ]);
            });
        });
    }
}
