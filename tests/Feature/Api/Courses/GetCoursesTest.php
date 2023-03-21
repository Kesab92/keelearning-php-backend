<?php

namespace Tests\Feature\Api\Courses;

use App\Models\Courses\Course;
use App\Models\Tag;
use App\Models\User;
use Tests\TestCase;

class GetCoursesTest extends TestCase
{
    private Tag $hasTag;
    private Tag $hasNotTag;

    public function setUp(): void
    {
        parent::setUp();

        $this->hasTag = Tag::factory()->create(['app_id' => $this->quizApp->id]);
        $this->hasNotTag = Tag::factory()->create(['app_id' => $this->quizApp->id]);

        $user = User::factory()->active()->create(['app_id' => $this->quizApp->id]);
        $user->tags()->sync([$this->hasTag->id]);
        $this->setAPIUser($user->id);
    }

    /**
     * Checks that courses with no tags are visible.
     *
     * @return void
     */
    public function testCoursesWithoutTagsAreVisible()
    {
        $course = Course::factory()->create([
            'app_id' => $this->quizApp->id,
            'visible' => 1,
        ]);
        $course->tags()->detach();
        $response = $this->json('GET', '/api/v1/courses');
        $response->assertJsonFragment([
                     'id' => $course->id,
                     'title' => $course->title,
                     'description' => $course->description,
                 ]);
    }

    /**
     * Checks that courses with owned tags are visible.
     *
     * @return void
     */
    public function testCoursesWithUsersTagsAreVisible()
    {
        $course = Course::factory()->create([
            'app_id' => $this->quizApp->id,
            'visible' => 1,
        ]);
        $course->tags()->sync([$this->hasTag->id]);
        $response = $this->json('GET', '/api/v1/courses');
        $response->assertJsonFragment([
            'id' => $course->id,
            'title' => $course->title,
            'description' => $course->description,
        ]);
    }

    /**
     * Checks that courses with not owned tags are invisible.
     *
     * @return void
     */
    public function testCoursesWithInaccessibleTagsAreInvisible()
    {
        $course = Course::factory()->create([
            'app_id' => $this->quizApp->id,
            'visible' => 1,
            'preview_enabled' => 0,
        ]);
        $course->tags()->sync([$this->hasNotTag->id]);
        $response = $this->json('GET', '/api/v1/courses');

        $this->assertCount(0, $response->original);
    }
}
