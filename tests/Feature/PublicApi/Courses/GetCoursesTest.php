<?php

namespace Tests\Feature\PublicApi\Courses;

use App\Models\Courses\Course;
use App\Models\Courses\CourseChapter;
use App\Models\Courses\CourseContent;
use App\Models\Tag;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Config;
use Illuminate\Testing\Fluent\AssertableJson;
use Tests\TestCase;
use Tests\UseSpectator;

class GetCoursesTest extends TestCase
{
    use UseSpectator;
    use DatabaseTransactions;

    /**
     * @return void
     */
    public function test_course_list_returns_correct_results()
    {
        Course::factory()
            ->count(5)
            ->create(['app_id' => $this->quizApp->id]);

        $response = $this
            ->actingAs(User::first())
            ->getJson('/api/public/v1/courses');

        $response
            ->assertValidRequest()
            ->assertValidResponse(200);

        $response->assertJson(function (AssertableJson $json) {
            $json->has(5)->first(function (AssertableJson $json) {
                $json
                    ->whereAllType([
                        'id' => 'integer',
                        'title' => 'string',
                        'is_visible' => 'boolean',
                        'is_mandatory' => 'boolean',
                        'max_duration' => 'integer',
                        'category_ids' => 'array',
                    ])
                    ->where('created_at', $this->isDate())
                    ->where('starts_at', $this->isDate(true))
                    ->where('duration_type', 'fixed')
                    ->where('participation_duration', null)
                    ->where('participation_duration_type', null)
                    ->where('ends_at', $this->isDate(true));
            });
        });
    }


    /**
     * @return void
     */
    public function test_dynamic_course_dates_are_returned_correctly()
    {
        $availableFrom = Carbon::now()->subDay();
        Course::factory()
            ->count(1)
            ->create([
                'app_id' => $this->quizApp->id,
                'available_from' => $availableFrom,
                'available_until' => Carbon::now()->addDay(),
                'duration_type' => Course::DURATION_TYPE_DYNAMIC,
                'participation_duration' => 4,
                'participation_duration_type' => Course::PARTICIPATION_DURATION_WEEKS,
            ]);

        $response = $this
            ->actingAs(User::first())
            ->getJson('/api/public/v1/courses');

        $response
            ->assertValidRequest()
            ->assertValidResponse(200);

        $response->assertJson(function (AssertableJson $json) use ($availableFrom) {
            $json->has(1)->first(function (AssertableJson $json) use ($availableFrom) {
                $json
                    ->whereAllType([
                        'id' => 'integer',
                        'title' => 'string',
                        'is_visible' => 'boolean',
                        'is_mandatory' => 'boolean',
                        'max_duration' => 'integer',
                        'category_ids' => 'array',
                    ])
                    ->where('created_at', $this->isDate())
                    ->where('starts_at', $this->isDate(false, $availableFrom))
                    ->where('ends_at', null)
                    ->where('duration_type', 'dynamic')
                    ->where('participation_duration', 4)
                    ->where('participation_duration_type', 'weeks');
            });
        });
    }

    /**
     * @return void
     */
    public function test_course_max_duration_calculation() {
        Config::set('app.force_language', 'de');
        Config::set('app.force_default_language', 'de');

        $tag = Tag::factory()
            ->create(['app_id' => $this->quizApp->id]);
        $course = Course::factory()
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
        $courseContent3 = CourseContent::factory()
            ->create([
                'course_chapter_id' => $courseChapter->id,
                'visible' => 0,
                'duration' => 7,
            ]);

        $totalDuration = $courseContent1->duration + $courseContent2->duration;

        $response = $this
            ->actingAs(User::first())
            ->getJson('/api/public/v1/courses');

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
