<?php

namespace Tests\Feature\BackendApi\Courses;

use App\Models\App;
use App\Models\ContentCategories\ContentCategory;
use App\Models\Courses\Course;
use App\Models\Tag;
use App\Services\AppSettings;
use Carbon\Carbon;
use Illuminate\Testing\Fluent\AssertableJson;
use Tests\TestCase;

class CourseListTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        $this->activateAllAppModules();
        $this->setBackendAPIUser($this->getMainAdmin());

        Course::factory()
            ->count(5)
            ->create([
                'app_id' => $this->quizAppId,
            ]);

        Course::factory()
            ->count(5)
            ->template()
            ->create([
                'app_id' => $this->quizAppId,
            ]);
    }
    public function test_access_denied_when_course_module_is_disabled()
    {
        $appSettings = new AppSettings($this->quizAppId);
        $appSettings->setValue('module_courses', 0);

        $this->json('GET', $this->backendAPIUrl('/courses'))
            ->assertStatus(403);
    }

    public function test_access_denied_for_admin_without_right()
    {
        $this->setBackendAPIUser($this->getPermissionslessAdmin());

        $this->json('GET', $this->backendAPIUrl('/courses'))
            ->assertStatus(403);
    }

    public function test_basic_course_list_works()
    {
        $secondQuizApp = App::factory()->create([
            'id' => 2,
            'app_hosted_at' => 'http://127.0.0.1',
        ]);

        Course::factory()
            ->count(5)
            ->create([
                'app_id' => $secondQuizApp->id,
            ]);

        $this->json('GET', $this->backendAPIUrl('/courses'))
            ->assertStatus(200)
            ->assertJson(function (AssertableJson $json) {
                $json
                    ->where('count', 5)
                    ->has('courses', 5)
                    ->has('courses.0', function (AssertableJson $json) {
                        $json
                            ->whereAll([
                                'is_repeating' => 0,
                                'is_template' => 0,
                                'latestRepeatedCourseCreatedAt' => null,
                                'next_repetition_date' => null,
                                'repetition_count' => null,
                                'repetition_interval' => null,
                                'repetition_interval_type' => null,
                            ])
                            ->whereAllType([
                                'archived_at' => 'string|null',
                                'available_from' => 'string|null',
                                'available_until' => 'string|null',
                                'categories' => 'array',
                                'cover_image_url' => 'string|null',
                                'created_at' => 'string',
                                'id' => 'integer',
                                'is_mandatory' => 'integer',
                                'is_repeating' => 'integer',
                                'is_template' => 'integer',
                                'latestRepeatedCourseCreatedAt' => 'null',
                                'next_repetition_date' => 'null',
                                'participations' => 'integer',
                                'repetition_count' => 'null',
                                'repetition_interval' => 'null',
                                'repetition_interval_type' => 'null',
                                'tags' => 'array',
                                'title' => 'string',
                                'visible' => 'integer',
                                'has_individual_attendees' => 'integer',
                                'individualAttendeesCount' => 'integer',
                            ]);
                    });
            });
    }

    public function test_active_course_list_works()
    {
        Course::factory()
            ->count(5)
            ->create([
                'app_id' => $this->quizAppId,
                'visible' => 1,
                'available_from' => Carbon::now()->subDays(5)
            ]);

        Course::factory()
            ->count(5)
            ->create([
                'app_id' => $this->quizAppId,
                'available_until' => Carbon::now()->addDays(5)
            ]);

        Course::factory()
            ->count(5)
            ->create([
                'app_id' => $this->quizAppId,
                'visible' => 0,
                'available_from' => Carbon::now()->subDays(5)
            ]);

        Course::factory()
            ->count(5)
            ->create([
                'app_id' => $this->quizAppId,
                'available_until' => Carbon::now()->subDays(5)
            ]);

        $this->json('GET', $this->backendAPIUrl('/courses?filter=active'))
            ->assertStatus(200)
            ->assertJson(function (AssertableJson $json) {
                $json
                    ->where('count', 15)
                    ->has('courses', 15)
                    ->has('courses.0', function (AssertableJson $json) {
                        $json
                            ->whereAll([
                                'is_repeating' => 0,
                                'is_template' => 0,
                                'latestRepeatedCourseCreatedAt' => null,
                                'next_repetition_date' => null,
                                'repetition_count' => null,
                                'repetition_interval' => null,
                                'repetition_interval_type' => null,
                            ])
                            ->whereAllType([
                                'archived_at' => 'string|null',
                                'available_from' => 'string|null',
                                'available_until' => 'string|null',
                                'categories' => 'array',
                                'cover_image_url' => 'string|null',
                                'created_at' => 'string',
                                'id' => 'integer',
                                'is_mandatory' => 'integer',
                                'is_repeating' => 'integer',
                                'is_template' => 'integer',
                                'latestRepeatedCourseCreatedAt' => 'null',
                                'next_repetition_date' => 'null',
                                'participations' => 'integer',
                                'repetition_count' => 'null',
                                'repetition_interval' => 'null',
                                'repetition_interval_type' => 'null',
                                'tags' => 'array',
                                'title' => 'string',
                                'visible' => 'integer',
                                'has_individual_attendees' => 'integer',
                                'individualAttendeesCount' => 'integer',
                            ]);
                    });
            });
    }

    public function test_expired_course_list_works()
    {
        Course::factory()
            ->count(5)
            ->create([
                'app_id' => $this->quizAppId,
                'visible' => 0,
                'available_from' => Carbon::now()->subDays(5)
            ]);

        Course::factory()
            ->count(5)
            ->create([
                'app_id' => $this->quizAppId,
                'available_until' => Carbon::now()->subDays(5)
            ]);

        $this->json('GET', $this->backendAPIUrl('/courses?filter=expired'))
            ->assertStatus(200)
            ->assertJson(function (AssertableJson $json) {
                $json
                    ->where('count', 10)
                    ->has('courses', 10)
                    ->has('courses.0', function (AssertableJson $json) {
                        $json
                            ->whereAll([
                                'is_repeating' => 0,
                                'is_template' => 0,
                                'latestRepeatedCourseCreatedAt' => null,
                                'next_repetition_date' => null,
                                'repetition_count' => null,
                                'repetition_interval' => null,
                                'repetition_interval_type' => null,
                            ])
                            ->whereAllType([
                                'archived_at' => 'string|null',
                                'available_from' => 'string|null',
                                'available_until' => 'string|null',
                                'categories' => 'array',
                                'cover_image_url' => 'string|null',
                                'created_at' => 'string',
                                'id' => 'integer',
                                'is_mandatory' => 'integer',
                                'is_repeating' => 'integer',
                                'is_template' => 'integer',
                                'latestRepeatedCourseCreatedAt' => 'null',
                                'next_repetition_date' => 'null',
                                'participations' => 'integer',
                                'repetition_count' => 'null',
                                'repetition_interval' => 'null',
                                'repetition_interval_type' => 'null',
                                'tags' => 'array',
                                'title' => 'string',
                                'visible' => 'integer',
                                'has_individual_attendees' => 'integer',
                                'individualAttendeesCount' => 'integer',
                            ]);
                    });
            });
    }

    public function test_archived_course_list_works()
    {
        Course::factory()
            ->count(5)
            ->create([
                'app_id' => $this->quizAppId,
                'archived_at' => Carbon::now(),
            ]);

        $this->json('GET', $this->backendAPIUrl('/courses?filter=archived'))
            ->assertStatus(200)
            ->assertJson(function (AssertableJson $json) {
                $json
                    ->where('count', 5)
                    ->has('courses', 5)
                    ->has('courses.0', function (AssertableJson $json) {
                        $json
                            ->whereAll([
                                'is_repeating' => 0,
                                'is_template' => 0,
                                'latestRepeatedCourseCreatedAt' => null,
                                'next_repetition_date' => null,
                                'repetition_count' => null,
                                'repetition_interval' => null,
                                'repetition_interval_type' => null,
                            ])
                            ->whereAllType([
                                'archived_at' => 'string',
                                'available_from' => 'string|null',
                                'available_until' => 'string|null',
                                'categories' => 'array',
                                'cover_image_url' => 'string|null',
                                'created_at' => 'string',
                                'id' => 'integer',
                                'is_mandatory' => 'integer',
                                'is_repeating' => 'integer',
                                'is_template' => 'integer',
                                'latestRepeatedCourseCreatedAt' => 'null',
                                'next_repetition_date' => 'null',
                                'participations' => 'integer',
                                'repetition_count' => 'null',
                                'repetition_interval' => 'null',
                                'repetition_interval_type' => 'null',
                                'tags' => 'array',
                                'title' => 'string',
                                'visible' => 'integer',
                                'has_individual_attendees' => 'integer',
                                'individualAttendeesCount' => 'integer',
                            ]);
                    });
            });
    }

    public function test_course_list_search_works()
    {
        $courses = Course::factory()
            ->count(5)
            ->create([
                'app_id' => $this->quizAppId,
                'title' => 'my_course_title_' . rand(),
            ]);

        $this->json('GET', $this->backendAPIUrl('/courses?search=title'))
            ->assertStatus(200)
            ->assertJson(function (AssertableJson $json) use ($courses) {
                $json
                    ->where('count', 5)
                    ->has('courses', 5)
                    ->has('courses.0', function (AssertableJson $json) use ($courses) {
                        $json
                            ->whereAll([
                                'is_repeating' => 0,
                                'is_template' => 0,
                                'latestRepeatedCourseCreatedAt' => null,
                                'next_repetition_date' => null,
                                'repetition_count' => null,
                                'repetition_interval' => null,
                                'repetition_interval_type' => null,
                                'title' => $courses[0]->title,
                            ])
                            ->whereAllType([
                                'archived_at' => 'string|null',
                                'available_from' => 'string|null',
                                'available_until' => 'string|null',
                                'categories' => 'array',
                                'cover_image_url' => 'string|null',
                                'created_at' => 'string',
                                'id' => 'integer',
                                'is_mandatory' => 'integer',
                                'is_repeating' => 'integer',
                                'is_template' => 'integer',
                                'latestRepeatedCourseCreatedAt' => 'null',
                                'next_repetition_date' => 'null',
                                'participations' => 'integer',
                                'repetition_count' => 'null',
                                'repetition_interval' => 'null',
                                'repetition_interval_type' => 'null',
                                'tags' => 'array',
                                'title' => 'string',
                                'visible' => 'integer',
                                'has_individual_attendees' => 'integer',
                                'individualAttendeesCount' => 'integer',
                            ]);
                    });
            });
    }

    public function test_search_by_tags()
    {
        $tag1 = Tag::factory()
            ->create(['app_id' => $this->quizAppId]);
        $tag2 = Tag::factory()
            ->create(['app_id' => $this->quizAppId]);
        $course1 = Course::factory()
            ->create(['app_id' => $this->quizAppId]);
        $course1->tags()->sync([$tag1->id]);
        $course2 = Course::factory()
            ->create(['app_id' => $this->quizAppId]);
        $course2->tags()->sync([$tag2->id]);

        $this->json('GET', $this->backendAPIUrl('/courses?tags[]=' . $tag1->id . '&tags[]=' . $tag2->id))
            ->assertStatus(200)
            ->assertJson(function (AssertableJson $json) {
                $json
                    ->where('count', 2)
                    ->has('courses', 2)
                    ->has('courses.0', function (AssertableJson $json) {
                        $json
                            ->whereAll([
                                'is_repeating' => 0,
                                'is_template' => 0,
                                'latestRepeatedCourseCreatedAt' => null,
                                'next_repetition_date' => null,
                                'repetition_count' => null,
                                'repetition_interval' => null,
                                'repetition_interval_type' => null,
                            ])
                            ->whereAllType([
                                'archived_at' => 'string|null',
                                'available_from' => 'string|null',
                                'available_until' => 'string|null',
                                'categories' => 'array',
                                'cover_image_url' => 'string|null',
                                'created_at' => 'string',
                                'id' => 'integer',
                                'is_mandatory' => 'integer',
                                'is_repeating' => 'integer',
                                'is_template' => 'integer',
                                'latestRepeatedCourseCreatedAt' => 'null',
                                'next_repetition_date' => 'null',
                                'participations' => 'integer',
                                'repetition_count' => 'null',
                                'repetition_interval' => 'null',
                                'repetition_interval_type' => 'null',
                                'tags' => 'array',
                                'title' => 'string',
                                'visible' => 'integer',
                                'has_individual_attendees' => 'integer',
                                'individualAttendeesCount' => 'integer',
                            ]);
                    });
            });
    }

    public function test_search_by_categories()
    {
        $category1 = ContentCategory::factory()
            ->create([
                'app_id' => $this->quizAppId,
                'type' => ContentCategory::TYPE_COURSES,
            ]);
        $category2 = ContentCategory::factory()
            ->create([
                'app_id' => $this->quizAppId,
                'type' => ContentCategory::TYPE_COURSES,
            ]);
        $course1 = Course::factory()
            ->create(['app_id' => $this->quizAppId]);
        $course1->categories()->syncWithPivotValues([$category1->id], [
            'type' => ContentCategory::TYPE_COURSES,
        ]);
        $course2 = Course::factory()
            ->create(['app_id' => $this->quizAppId]);
        $course2->categories()->syncWithPivotValues([$category2->id], [
            'type' => ContentCategory::TYPE_COURSES,
        ]);

        $this->json('GET', $this->backendAPIUrl('/courses?categories[]=' . $category1->id . '&categories[]=' . $category2->id))
            ->assertStatus(200)
            ->assertJson(function (AssertableJson $json) {
                $json
                    ->where('count', 2)
                    ->has('courses', 2)
                    ->has('courses.0', function (AssertableJson $json) {
                        $json
                            ->whereAll([
                                'is_repeating' => 0,
                                'is_template' => 0,
                                'latestRepeatedCourseCreatedAt' => null,
                                'next_repetition_date' => null,
                                'repetition_count' => null,
                                'repetition_interval' => null,
                                'repetition_interval_type' => null,
                            ])
                            ->whereAllType([
                                'archived_at' => 'string|null',
                                'available_from' => 'string|null',
                                'available_until' => 'string|null',
                                'categories' => 'array',
                                'cover_image_url' => 'string|null',
                                'created_at' => 'string',
                                'id' => 'integer',
                                'is_mandatory' => 'integer',
                                'is_repeating' => 'integer',
                                'is_template' => 'integer',
                                'latestRepeatedCourseCreatedAt' => 'null',
                                'next_repetition_date' => 'null',
                                'participations' => 'integer',
                                'repetition_count' => 'null',
                                'repetition_interval' => 'null',
                                'repetition_interval_type' => 'null',
                                'tags' => 'array',
                                'title' => 'string',
                                'visible' => 'integer',
                                'has_individual_attendees' => 'integer',
                                'individualAttendeesCount' => 'integer',
                            ]);
                    });
            });
    }

    public function test_search_by_everything()
    {
        $tag = Tag::factory()
            ->create(['app_id' => $this->quizAppId]);
        $category = ContentCategory::factory()
            ->create([
                'app_id' => $this->quizAppId,
                'type' => ContentCategory::TYPE_COURSES,
            ]);

        $course = Course::factory()
            ->create(['app_id' => $this->quizAppId]);
        $course->categories()->syncWithPivotValues([$category->id], [
            'type' => ContentCategory::TYPE_COURSES,
        ]);
        $course->tags()->sync([$tag->id]);

        $this->json('GET', $this->backendAPIUrl('/courses?categories[]=' . $category->id . '&tags[]=' . $tag->id . '&search=' . $course->title))
            ->assertStatus(200)
            ->assertJson(function (AssertableJson $json) use ($course) {
                $json
                    ->where('count', 1)
                    ->has('courses', 1)
                    ->has('courses.0', function (AssertableJson $json) use ($course) {
                        $json
                            ->whereAll([
                                'is_repeating' => 0,
                                'is_template' => 0,
                                'latestRepeatedCourseCreatedAt' => null,
                                'next_repetition_date' => null,
                                'repetition_count' => null,
                                'repetition_interval' => null,
                                'repetition_interval_type' => null,
                                'title' => $course->title,
                            ])
                            ->whereAllType([
                                'archived_at' => 'string|null',
                                'available_from' => 'string|null',
                                'available_until' => 'string|null',
                                'categories' => 'array',
                                'cover_image_url' => 'string|null',
                                'created_at' => 'string',
                                'id' => 'integer',
                                'is_mandatory' => 'integer',
                                'is_repeating' => 'integer',
                                'is_template' => 'integer',
                                'latestRepeatedCourseCreatedAt' => 'null',
                                'next_repetition_date' => 'null',
                                'participations' => 'integer',
                                'repetition_count' => 'null',
                                'repetition_interval' => 'null',
                                'repetition_interval_type' => 'null',
                                'tags' => 'array',
                                'title' => 'string',
                                'visible' => 'integer',
                                'has_individual_attendees' => 'integer',
                                'individualAttendeesCount' => 'integer',
                            ]);
                    });
            });
    }
}
