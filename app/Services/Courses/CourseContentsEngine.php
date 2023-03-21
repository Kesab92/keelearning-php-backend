<?php

namespace App\Services\Courses;

use App\Models\CertificateTemplate;
use App\Models\Courses\Course;
use App\Models\Courses\CourseChapter;
use App\Models\Courses\CourseContent;
use App\Models\LearningMaterial;
use App\Models\Question;
use App\Models\Todolist;
use App\Services\TranslationEngine;
use App\Transformers\BackendApi\Todolists\TodolistItemEditTransformer;
use DB;
use Illuminate\Support\Collection;

class CourseContentsEngine
{
    public function createChapter(Course $course, $previousChapterId = null) : CourseChapter
    {
        $position = 0;
        if ($previousChapterId) {
            $previousChapter = $this->findChapter($course, $previousChapterId);

            $this->moveLaterChaptersDown($course, $previousChapter->position);
            $position = $previousChapter->position + 1;
        }

        $chapterCount = $course->chapters()->count();
        $chapter = new CourseChapter();
        $chapter->position = $position;
        $course->chapters()->save($chapter);
        $chapter->setLanguage(defaultAppLanguage($course->app_id));
        $chapter->title = 'Kapitel '.($chapterCount + 1);
        $chapter->save();

        return $chapter;
    }

    public function createContent(Course $course, int $type, $chapterId, $position) : CourseContent
    {
        $chapter = $this->findChapter($course, $chapterId);

        $this->moveContentsDown($chapter, $position);

        $content = new CourseContent();
        $content->type = $type;
        $content->position = $position;
        $content->duration = 1;
        $content->setLanguage(defaultAppLanguage($course->app_id));

        $chapter->contents()->save($content);

        // Create the language relation
        $content->title = '';

        if($type === CourseContent::TYPE_TODOLIST) {
            $todolist = new Todolist();
            $todolist->app_id = $course->app_id;
            $todolist->foreign_type = Todolist::TYPE_COURSE_CONTENT;
            $todolist->foreign_id = $content->id;
            $todolist->save();

            $content->foreign_id = $todolist->id;
        }
        $content->save();

        return $content;
    }

    private function moveLaterChaptersDown(Course $course, $chapterPosition)
    {
        $course->chapters()
            ->where('position', '>', $chapterPosition)
            ->rawUpdate([
                'position' => DB::raw('position + 1'),
            ]);
    }

    private function moveContentsDown(CourseChapter $chapter, $startingPosition)
    {
        $chapter->contents()
            ->where('position', '>=', $startingPosition)
            ->rawUpdate([
                'position' => DB::raw('position + 1'),
            ]);
    }

    private function findChapter(Course $course, $chapterId) : CourseChapter
    {
        $chapter = $course->chapters->find($chapterId);
        if (! $chapter) {
            app()->abort(404);
        }

        return $chapter;
    }

    public function getContent(Course $course, $contentId)
    {
        /** @var CourseContent $content */
        $content = $course->contents()->where('course_contents.id', $contentId)->first();
        if (! $content) {
            app()->abort(404);
        }

        $content->load('tags');

        switch ($content->type) {
            case CourseContent::TYPE_LEARNINGMATERIAL:
                return $this->getLearningmaterialContent($course, $content);
            case CourseContent::TYPE_APPOINTMENT:
            case CourseContent::TYPE_FORM:
            case CourseContent::TYPE_TODOLIST:
                return ['content' => $content];
            case CourseContent::TYPE_QUESTIONS:
                return $this->getQuestionsContent($course, $content);
            case CourseContent::TYPE_CERTIFICATE:
                return $this->getCertificateContent($course, $content);
        }

        return null;
    }

    private function getLearningmaterialContent(Course $course, CourseContent $content)
    {
        $learningmaterial = null;
        if ($content->foreign_id) {
            /** @var LearningMaterial $learningmaterial */
            $learningmaterial = LearningMaterial
                ::select('learning_materials.*')
                ->join('learning_material_folders', 'learning_material_folders.id', '=', 'learning_materials.learning_material_folder_id')
                ->where('learning_material_folders.app_id', $course->app_id)
                ->where('learning_materials.id', $content->foreign_id)
                ->first();
        }
        if ($learningmaterial) {
            $learningmaterial->load('translationRelation');
        }

        return [
            'content' => $content,
            'learningmaterial' => $learningmaterial,
        ];
    }

    private function getQuestionsContent(Course $course, CourseContent $content)
    {
        /** @var TranslationEngine $translationEngine */
        $translationEngine = app(TranslationEngine::class);
        $attachments = $content->attachments;

        $questions = Question::where('app_id', $course->app_id)
            ->whereIn('id', $attachments->pluck('foreign_id'))
            ->with('category.translationRelation')
            ->get()
            ->keyBy('id');
        $questions = $translationEngine->attachQuestionTranslations($questions, $course->app);

        $attachments = $attachments->map(function ($attachment) use ($questions) {
            $question = $questions->get($attachment->foreign_id);
            if (! $question) {
                return null;
            }

            return [
                'id' => $attachment->id,
                'position' => $attachment->position,
                'question' => [
                    'id' => $question->id,
                    'title' => $question->title,
                    'category' => $question->category ? $question->category->name : '',
                    'type' => $question->getTypeLabel(),
                ],
            ];
        });

        // Remove attachments without question
        $attachments = $attachments->filter()->values();

        return [
            'content' => $content,
            'attachments' => $attachments,
        ];
    }

    private function getCertificateContent(Course $course, CourseContent $content)
    {
        $certificate = null;
        if ($content->foreign_id) {
            /** @var LearningMaterial $learningmaterial */
            $certificate = CertificateTemplate::findOrFail($content->foreign_id);
        }

        return [
            'content' => $content,
            'certificate' => $certificate,
        ];
    }

    public function getAvailableLearningmaterials(Course $course, string $language) : Collection
    {
        $query = LearningMaterial
            ::select('learning_materials.*')
            ->join('learning_material_folders', 'learning_material_folders.id', '=', 'learning_materials.learning_material_folder_id')
            ->where('learning_material_folders.app_id', $course->app_id);

        $loadRelations = ['translationRelation', 'learningMaterialFolder'];
        if ($language != defaultAppLanguage($course->app_id)) {
            $loadRelations[] = 'defaultTranslationRelation';
        }

        return $query
            ->with($loadRelations)
            ->get()
            ->map(function ($learningmaterial) {
                return $learningmaterial->only(['id', 'cover_image_url', 'title', 'description']);
            })
            ->sortBy('title', SORT_NATURAL|SORT_FLAG_CASE)
            ->values();
    }
}
