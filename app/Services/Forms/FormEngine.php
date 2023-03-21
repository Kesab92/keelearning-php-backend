<?php

namespace App\Services\Forms;

use App\Models\Courses\Course;
use App\Models\Courses\CourseChapter;
use App\Models\Courses\CourseContent;
use App\Models\Forms\Form;
use App\Models\Forms\FormAnswer;
use App\Models\Forms\FormAnswerField;
use App\Models\Forms\FormField;
use App\Models\User;
use App\Transformers\BackendApi\Forms\FormDetailPageTransformer;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class FormEngine
{

    /**
     * Creates a query for forms using filter
     * @param int $appId
     * @param User $admin
     * @param string|null $search
     * @param array $tags
     * @param array $categories
     * @param string|null $filter
     * @param string|null $orderBy
     * @param false $descending
     * @return Builder|Form
     */
    public function formsFilterQuery(int $appId, User $admin, string $search = null, array $tags = [], array $categories = [], string $filter = null, string $orderBy = null, bool $descending = false)
    {
        $formsQuery = Form::where('app_id', $appId);
        $formsQuery->tagRights($admin);

        if ($search) {
            $matchingTitles = DB::table('form_translations')
                ->join('forms', 'form_translations.form_id', '=', 'forms.id')
                ->select('forms.id')
                ->where('forms.app_id', $appId)
                ->where('form_translations.title', 'LIKE', '%' . $search . '%');
            $formsQuery->where(function ($query) use ($search, $matchingTitles) {
                $query->whereIn('forms.id', $matchingTitles)
                    ->orWhere('forms.id', extractHashtagNumber($search));
            });
        }

        switch($filter) {
            case 'active':
                $formsQuery->where('is_archived', 0);
                break;
            case 'archived':
                $formsQuery->where('is_archived', 1);
                break;
        }

        if ($tags && count($tags)) {
            $formsWithoutTag = in_array(-1, $tags);
            $tags = array_filter($tags, function ($tag) {
                return $tag !== '-1';
            });
            if (count($tags)) {
                $formsQuery->where(function (Builder $query) use ($tags, $formsWithoutTag) {
                    $query->whereHas('tags', function ($query) use ($tags) {
                        $query->whereIn('tags.id', $tags);
                    });
                    if ($formsWithoutTag) {
                        $query->orWhereDoesntHave('tags');
                    }
                });
            } else {
                $formsQuery->doesntHave('tags');
            }
        }

        if ($categories && count($categories)) {
            $formsQuery->where(function (Builder $query) use ($categories) {
                $query->whereHas('categories', function ($query) use ($categories) {
                    $query->whereIn('content_categories.id', $categories);
                });
            });
        }

        if ($orderBy) {
            switch ($orderBy) {
                case 'title':
                    $formsQuery = Form::orderByTranslatedField($formsQuery, 'title', $appId, $descending ? 'desc' : 'asc');
                    break;
                default:
                    $formsQuery->orderBy($orderBy, $descending ? 'desc' : 'asc');
                    break;
            }
        }

        return $formsQuery;
    }

    /**
     * Returns the form
     *
     * @param int $formId
     * @param int $appId
     * @param User $admin
     * @return Form
     */
    public function getForm(int $formId, int $appId, User $admin):Form
    {
        $form = Form::tagRights($admin)->findOrFail($formId);

        // Check the access rights
        if ($form->app_id != $appId) {
            app()->abort(403);
        }
        return $form;
    }

    /**
     * Returns the form field
     *
     * @param int $formFieldId
     * @param int $formId
     * @return FormField
     */
    public function getFormField(int $formFieldId, int $formId):FormField
    {
        $formField = FormField::findOrFail($formFieldId);

        // Check the access
        if ($formField->form_id != $formId) {
            app()->abort(403);
        }
        return $formField;
    }

    /**
     * Returns the form response
     * @param Form $form
     * @return array
     */
    public function getFormResponse(Form $form):array {

        $formDetailPageTransformer = app(FormDetailPageTransformer::class);
        return $formDetailPageTransformer->transform($form);
    }

    /**
     * Returns the form usages
     * @param Form $form
     */
    public function getUsages(Form $form) {
        $courseIds = CourseChapter
            ::select('course_chapters.*')
            ->leftJoin('course_contents', 'course_chapters.id', '=', 'course_contents.course_chapter_id')
            ->where('course_contents.type', CourseContent::TYPE_FORM)
            ->where('course_contents.foreign_id', $form->id)
            ->get()
            ->pluck('course_id')
            ->unique();

        return Course
            ::whereIn('id', $courseIds)
            ->with('translationRelation')
            ->get();

    }

    /**
     * Saves the user answer for the form.
     * @param int $formId
     * @param User $user
     * @param array $answerFields
     * @param int|null $foreignType
     * @param int|null $foreignId
     * @return void
     */
    public function saveFormAnswer(int $formId, User $user, array $answerFields, int $foreignType = null, int $foreignId = null) {
        /** @var Form $form */
        $form = Form
            ::where('id', $formId)
            ->where('app_id', $user->app_id)
            ->first();

        if (! $form) {
            app()->abort(404);
        }

        if($foreignType && $foreignId) {
            $hasAnswer = FormAnswer
                ::where('foreign_type', $foreignType)
                ->where('foreign_id', $foreignId);
        } else {
            $hasAnswer = FormAnswer
                ::where('form_id', $formId);
        }
        $hasAnswer = $hasAnswer
            ->where('user_id', $user->id)
            ->exists();

        if ($hasAnswer) {
            app()->abort(403);
        }

        $formAnswer = new FormAnswer();
        $formAnswer->form_id = $formId;
        $formAnswer->user_id = $user->id;

        if($foreignType && $foreignId) {
            $formAnswer->foreign_id = $foreignId;
            $formAnswer->foreign_type = $foreignType;
        }

        $formAnswer->save();

        foreach ($answerFields as $formFieldId => $answerField) {
            $formAnswerField = new FormAnswerField();
            $formAnswerField->answer = $answerField;
            $formAnswerField->form_answer_id = $formAnswer->id;
            $formAnswerField->form_field_id = $formFieldId;
            $formAnswerField->save();
        }

    }
}
