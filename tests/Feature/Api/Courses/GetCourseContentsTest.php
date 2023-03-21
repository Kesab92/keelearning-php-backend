<?php

namespace Tests\Feature\Api\Courses;

use App\Models\Courses\Course;
use App\Models\Courses\CourseChapter;
use App\Models\Courses\CourseContent;
use App\Models\Forms\Form;
use App\Models\Forms\FormField;
use App\Models\LearningMaterial;
use App\Models\LearningMaterialFolder;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Support\Facades\Config;
use Tests\TestCase;

class GetCourseContentsTest extends TestCase
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

        Config::set('app.force_language', 'de');
        Config::set('app.force_default_language', 'de');

        $this->course = Course::factory()->create([
            'app_id' => $this->quizApp->id,
            'visible' => 1,
        ]);
        $this->course->tags()->detach();

        $this->courseChapter = CourseChapter::factory()->create([
            'course_id' => $this->course->id,
        ]);

        $learningMaterialFolder = LearningMaterialFolder::factory()->create([
            'app_id' => $this->quizApp->id,
        ]);

        $this->learningMaterial = LearningMaterial::factory()->create([
            'learning_material_folder_id' => $learningMaterialFolder->id,
            'visible' => 1,
        ]);
        $this->learningMaterial->tags()->detach();
    }

    /**
     * Checks that course contents with no tags are visible.
     *
     * @return void
     */
    public function testCourseContentsWithoutTagsAreVisible()
    {
        $courseContent = CourseContent::factory()->create([
            'course_chapter_id' => $this->courseChapter->id,
            'type' => CourseContent::TYPE_LEARNINGMATERIAL,
            'foreign_id' => $this->learningMaterial->id,
            'visible' => 1,
        ]);
        $courseContent->tags()->detach();

        $response = $this->json('GET', '/api/v1/courses/' . $this->course->id . '/contents');
        $response->assertJsonFragment([
                'id' => $courseContent->id,
                'title' => $courseContent->title,
                'description' => $courseContent->description,
            ]
        );
    }

    /**
     * Checks that course contents with owned tags are visible.
     *
     * @return void
     */
    public function testCourseContentsWithUsersTagsAreVisible()
    {
        $courseContent = CourseContent::factory()->create([
            'course_chapter_id' => $this->courseChapter->id,
            'type' => CourseContent::TYPE_LEARNINGMATERIAL,
            'foreign_id' => $this->learningMaterial->id,
            'visible' => 1,
        ]);
        $courseContent->tags()->sync([$this->hasTag->id]);

        $response = $this->json('GET', '/api/v1/courses/' . $this->course->id . '/contents');
        $response->assertJsonFragment([
                'id' => $courseContent->id,
                'title' => $courseContent->title,
                'description' => $courseContent->description,
            ]
        );
    }

    /**
     * Checks that course contents with owned tags are visible.
     *
     * @return void
     */
    public function testCourseContentsWithInaccessibleTagsAreInvisible()
    {
        $courseContent = CourseContent::factory()->create([
            'course_chapter_id' => $this->courseChapter->id,
            'type' => CourseContent::TYPE_LEARNINGMATERIAL,
            'foreign_id' => $this->learningMaterial->id,
            'visible' => 1,
        ]);
        $courseContent->tags()->sync([$this->hasNotTag->id]);

        $courseContent2 = CourseContent::factory()->create([
            'course_chapter_id' => $this->courseChapter->id,
            'type' => CourseContent::TYPE_LEARNINGMATERIAL,
            'foreign_id' => $this->learningMaterial->id,
            'visible' => 1,
        ]);
        $courseContent2->tags()->sync([$this->hasTag->id]);

        $response = $this->json('GET', '/api/v1/courses/' . $this->course->id . '/contents');

        $this->assertCount(1, $response->original['course']['chapters'][0]['contents']);
        $this->assertEquals($courseContent2->id, $response->original['course']['chapters'][0]['contents'][0]['id']);
        $this->assertEquals($courseContent2->title, $response->original['course']['chapters'][0]['contents'][0]['title']);
        $this->assertEquals($courseContent2->description, $response->original['course']['chapters'][0]['contents'][0]['description']);
    }

    /**
     * Checks that course contents with owned tags are visible.
     *
     * @return void
     */
    public function testFormCourseContent()
    {
        $form = Form::factory()->create();
        $form->tags()->detach();

        $formField = FormField::factory()->create([
            'form_id' => $form->id,
            'position' => 2,
        ]);
        $formField2 = FormField::factory()->create([
            'form_id' => $form->id,
            'position' => 1,
        ]);

        $courseContent = CourseContent::factory()->create([
            'course_chapter_id' => $this->courseChapter->id,
            'type' => CourseContent::TYPE_FORM,
            'foreign_id' => $form->id,
            'visible' => 1,
        ]);
        $courseContent->tags()->sync([$this->hasTag->id]);

        $response = $this->json('GET', '/api/v1/courses/' . $this->course->id . '/contents');
        $response->assertJsonFragment([
                'id' => $courseContent->id,
                'title' => $courseContent->title,
                'description' => $courseContent->description,
                'relatable' => [
                    'cover_image_url' => '',
                    'fields' => [
                        0 => [
                            'id' => $formField2->id,
                            'is_required' => $formField2->is_required,
                            'position' => $formField2->position,
                            'title' => $formField2->title,
                            'type' => $formField2->type,
                        ],
                        1 => [
                            'id' => $formField->id,
                            'is_required' => $formField->is_required,
                            'position' => $formField->position,
                            'title' => $formField->title,
                            'type' => $formField->type,
                        ]
                    ],
                    'id' => $form->id,
                    'title' => $courseContent->title,
                ]
            ]
        );
    }
}
