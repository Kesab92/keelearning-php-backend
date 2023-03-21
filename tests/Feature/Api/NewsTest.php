<?php

namespace Tests\Feature\Api;

use App\Models\News;
use App\Models\Tag;
use App\Models\User;
use Tests\TestCase;

class NewsTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        $this->hasTag = Tag::factory()->create(['app_id' => $this->quizApp->id]);
        $this->hasNotTag = Tag::factory()->create(['app_id' => $this->quizApp->id]);

        $this->user = User::factory()->active()->create(['app_id' => $this->quizApp->id]);
        $this->user->tags()->sync([$this->hasTag->id]);
        $this->setAPIUser($this->user->id);
    }

    /**
     * Checks that news with no tags are visible.
     *
     * @return void
     */
    public function testNewsWithoutTagsAreVisible()
    {
        $news = News::factory()->published()->create(['app_id' => $this->quizApp->id]);
        $news->tags()->detach();
        $response = $this->json('GET', '/api/v1/news');
        $response->assertJsonFragment([
                     'id' => $news->id,
                     'title' => $news->title,
                     'content' => $news->content,
                 ]);
    }

    /**
     * Checks that news with owned tags are visible.
     *
     * @return void
     */
    public function testNewsWithUsersTagsAreVisible()
    {
        $news = News::factory()->published()->create(['app_id' => $this->quizApp->id]);
        $news->tags()->sync([$this->hasTag->id]);
        $response = $this->json('GET', '/api/v1/news');
        $response->assertJsonFragment([
                     'id' => $news->id,
                     'title' => $news->title,
                     'content' => $news->content,
                 ]);
    }

    /**
     * Checks that news with not owned tags are invisible.
     *
     * @return void
     */
    public function testNewsWithInaccessibleTagsAreInvisible()
    {
        $news = News::factory()->published()->create(['app_id' => $this->quizApp->id]);
        $news->tags()->sync([$this->hasNotTag->id]);
        $response = $this->json('GET', '/api/v1/news');
        $this->assertFalse(in_array([
                     'id' => $news->id,
                     'title' => $news->title,
                     'content' => $news->content,
                 ], $response->original['news'], true));
    }
}
