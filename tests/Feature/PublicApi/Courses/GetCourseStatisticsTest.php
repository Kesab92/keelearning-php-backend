<?php

namespace Tests\Feature\PublicApi\Courses;

use App\Models\Courses\Course;
use App\Models\Courses\CourseChapter;
use App\Models\Courses\CourseContent;
use App\Models\Courses\CourseContentAttempt;
use App\Models\Courses\CourseParticipation;
use App\Models\Tag;
use App\Models\User;
use Carbon\Carbon;
use Config;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Testing\Fluent\AssertableJson;
use Tests\TestCase;
use Tests\UseSpectator;

class GetCourseStatisticsTest extends TestCase
{
    use UseSpectator;
    use DatabaseTransactions;

    /**
     * @return void
     */
    public function test_course_statistic_list_returns_correct_results()
    {
        Config::set('app.force_language', 'de');
        Config::set('app.force_default_language', 'de');
        $user1 = User::factory()
            ->create(['app_id' => $this->quizApp->id]);
        $user2 = User::factory()
            ->create(['app_id' => $this->quizApp->id]);
        $course = Course::factory()
            ->create(['app_id' => $this->quizApp->id]);
        $courseChapter = CourseChapter::factory()
            ->create(['course_id' => $course->id]);
        $courseContent = CourseContent::factory()
            ->certificate()
            ->create(['course_chapter_id' => $courseChapter->id]);
        $participation1 = CourseParticipation::factory()
            ->create([
                'course_id' => $course->id,
                'user_id' => $user1->id,
            ]);
        $participation2 = CourseParticipation::factory()
            ->create([
                'course_id' => $course->id,
                'user_id' => $user2->id,
                'created_at' => Carbon::now()->addMinute(),
                'updated_at' => Carbon::now()->addMinute(),
            ]);

        $contentAttempt = CourseContentAttempt::factory()
            ->passed()
            ->create([
                'course_content_id' => $courseContent->id,
                'course_participation_id' => $participation1->id,
            ]);

        Config::set('app.force_language', null);
        Config::set('app.force_default_language', null);

        $response = $this
            ->actingAs(User::first())
            ->getJson('/api/public/v1/courses/' . $course->id . '/statistics');

        $response
            ->assertValidRequest()
            ->assertValidResponse(200);

        $response->assertJson(function (AssertableJson $json) use ($user1, $contentAttempt, $courseContent) {
            $json->has(2)->first(function (AssertableJson $json) use ($user1, $contentAttempt, $courseContent) {
                $json
                    ->whereAllType([
                        'id' => 'integer',
                        'course_id' => 'integer',
                        'status' => 'string',
                        'certificates' => 'array',
                        'duration' => 'integer',
                    ])
                    ->where('user_id', $user1->id)
                    ->where('started_at', $this->isDate())
                    ->where('finished_at', $this->isDate(true))
                    ->where('updated_at', $this->isDate())
                    ->has('certificates', 1, function (AssertableJson $json) use ($contentAttempt, $courseContent) {
                        $json
                            ->whereAll([
                                'id' => $contentAttempt->id,
                                'course_content_id' => $courseContent->id,
                            ])
                            ->where('download_url', function($value) {
                                return strpos($value, 'signature=') !== false;
                            });
                    });
            });
        });

        // Test sorting by updated_at works
        $sortedResponse1 = $this
            ->actingAs(User::first())
            ->getJson('/api/public/v1/courses/' . $course->id . '/statistics?orderBy=updated_at_desc');

        $sortedResponse1
            ->assertValidRequest()
            ->assertValidResponse(200);

        $sortedResponse1->assertJson(function (AssertableJson $json) use ($user2, $contentAttempt, $courseContent) {
            $json->has(2)->first(function (AssertableJson $json) use ($user2, $contentAttempt, $courseContent) {
                $json
                    ->where('user_id', $user2->id)
                    ->etc();
            });
        });

        // Test sorting by started_at works
        $sortedResponse2 = $this
            ->actingAs(User::first())
            ->getJson('/api/public/v1/courses/' . $course->id . '/statistics?orderBy=started_at_desc');

        $sortedResponse2
            ->assertValidRequest()
            ->assertValidResponse(200);

        $sortedResponse2->assertJson(function (AssertableJson $json) use ($user2, $contentAttempt, $courseContent) {
            $json->has(2)->first(function (AssertableJson $json) use ($user2, $contentAttempt, $courseContent) {
                $json
                    ->where('user_id', $user2->id)
                    ->etc();
            });
        });
    }

    public function test_course_total_duration_calculation() {
        Config::set('app.force_language', 'de');
        Config::set('app.force_default_language', 'de');

        $tag1 = Tag::factory()
            ->create(['app_id' => $this->quizApp->id]);
        $tag2 = Tag::factory()
            ->create(['app_id' => $this->quizApp->id]);
        $user = User::factory()
            ->create(['app_id' => $this->quizApp->id]);
        $user->tags()->sync([$tag2->id]);

        $course = Course::factory()
            ->create(['app_id' => $this->quizApp->id]);
        $courseChapter = CourseChapter::factory()
            ->create(['course_id' => $course->id]);

        $courseContent1 = CourseContent::factory()
            ->create([
                'course_chapter_id' => $courseChapter->id,
                'duration' => 5,
            ]);
        $courseContent1->tags()->sync([$tag1->id]);
        $courseContent2 = CourseContent::factory()
            ->certificate()
            ->create([
                'course_chapter_id' => $courseChapter->id,
                'duration' => 7,
            ]);
        $courseContent2->tags()->sync([$tag2->id]);
        $courseContent3 = CourseContent::factory()
            ->certificate()
            ->create([
                'course_chapter_id' => $courseChapter->id,
                'duration' => 4,
            ]);
        $courseContent3->tags()->sync([$tag2->id]);

        $participation = CourseParticipation::factory()
            ->create([
                'course_id' => $course->id,
                'user_id' => $user->id,
            ]);

        CourseContentAttempt::factory()
            ->passed()
            ->create([
                'course_content_id' => $courseContent1->id,
                'course_participation_id' => $participation->id,
            ]);
        CourseContentAttempt::factory()
            ->passed()
            ->create([
                'course_content_id' => $courseContent2->id,
                'course_participation_id' => $participation->id,
            ]);
        CourseContentAttempt::factory()
            ->create([
                'course_content_id' => $courseContent3->id,
                'course_participation_id' => $participation->id,
                'passed' => 0,
            ]);

        $totalDuration = $courseContent1->duration + $courseContent2->duration;

        $response = $this
            ->actingAs($user)
            ->getJson('/api/public/v1/courses/' . $course->id . '/statistics');

        $response
            ->assertValidRequest()
            ->assertValidResponse(200);

        $response->assertJson(function (AssertableJson $json) use ($totalDuration) {
            $json->has(1)->first(function (AssertableJson $json) use ($totalDuration) {
                $json
                    ->where('duration', $totalDuration)
                    ->etc();
            });
        });
    }
}
