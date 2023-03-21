<?php

namespace Tests\Feature\PublicApi\Courses;

use App\Models\Courses\Course;
use App\Models\Courses\CourseChapter;
use App\Models\Courses\CourseContent;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Config;
use Illuminate\Testing\Fluent\AssertableJson;
use Tests\TestCase;
use Tests\UseSpectator;

class GetCourseTemplatesTest extends TestCase
{
    use UseSpectator;
    use DatabaseTransactions;

    /**
     * @return void
     */
    public function test_course_template_list_returns_correct_results()
    {
        Course::factory()
            ->template()
            ->count(5)
            ->create(['app_id' => $this->quizApp->id]);

        $response = $this
            ->actingAs(User::first())
            ->getJson('/api/public/v1/course-templates');

        $response
            ->assertValidRequest()
            ->assertValidResponse(200);

        $response->assertJson(function (AssertableJson $json) {
            $json->has(5)->first(function (AssertableJson $json) {
                $json
                    ->whereAllType([
                        'id' => 'integer',
                        'title' => 'string',
                        'is_active' => 'boolean',
                        'is_mandatory' => 'boolean',
                        'max_duration' => 'integer',
                        'category_ids' => 'array',
                    ])
                    ->where('created_at', $this->isDate())
                    ->where('starts_at', $this->isDate(true));
            });
        });
    }

    /**
     * @return void
     */
    public function test_course_template_max_duration_calculation() {
        Config::set('app.force_language', 'de');
        Config::set('app.force_default_language', 'de');

        $tag = Tag::factory()
            ->create(['app_id' => $this->quizApp->id]);
        $course = Course::factory()
            ->template()
            ->create(['app_id' => $this->quizApp->id]);
        $courseChapter = CourseChapter::factory()
            ->create(['course_id' => $course->id]);
        $courseContent1 = CourseContent::factory()
            ->create([
                'course_chapter_id' => $courseChapter->id,
                'visible' => 1,
                'duration' => 5,
            ]);
        $courseContent1->tags()->sync([$tag->id]);
        $courseContent2 = CourseContent::factory()
            ->create([
                'course_chapter_id' => $courseChapter->id,
                'visible' => 1,
                'duration' => 7,
            ]);
        CourseContent::factory()
            ->create([
                'course_chapter_id' => $courseChapter->id,
                'visible' => 0,
                'duration' => 7,
            ]);

        $totalDuration = $courseContent1->duration + $courseContent2->duration;

        $response = $this
            ->actingAs(User::first())
            ->getJson('/api/public/v1/course-templates');

        $response
            ->assertValidRequest()
            ->assertValidResponse(200);

        $response->assertJson(function (AssertableJson $json) use ($totalDuration) {
            $json->has(1)->first(function (AssertableJson $json) use ($totalDuration) {
                $json
                    ->where('max_duration', $totalDuration)
                    ->etc();
            });
        });
    }
}
