<?php

namespace App\Exports;

use App\Models\Courses\Course;
use App\Models\Courses\CourseContent;
use App\Models\Forms\Form;
use App\Models\Forms\FormField;
use App\Models\User;
use App\Services\Courses\CourseStatisticsEngine;
use Illuminate\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class CourseFormStatistics implements FromView, ShouldAutoSize
{

    private User $admin;
    private CourseContent $content;
    private Course $course;
    private CourseStatisticsEngine $courseStatisticsEngine;
    private Form $form;
    private bool $showEmails;
    private bool $showPersonalData;

    public function __construct(int $courseId, int $contentId, User $admin, $showPersonalData = false, $showEmails = false)
    {
        $this->course = Course::findOrFail($courseId);
        $this->content = $this->course->contents()->where('course_contents.id', $contentId)->first();
        if(!$this->content) {
            app()->abort(404);
        }
        $this->form = Form::where('app_id', appId())->findOrFail($this->content->relatable->id);
        $this->admin = $admin;
        $this->showEmails = $showEmails;
        $this->showPersonalData = $showPersonalData;
        $this->courseStatisticsEngine = app(CourseStatisticsEngine::class);
    }

    public function view(): View
    {
        return view('stats.courses.csv.form', $this->getData());
    }

    private function getData() {
        $formAnswers = $this->courseStatisticsEngine->getFormAnswers($this->course, $this->content, $this->admin, '', [], 'id', null, null, null, $this->showPersonalData, $this->showEmails);

        $formAnswers = $formAnswers['formAnswers']->map(function($formAnswer) {
            return [
                'fields' => $formAnswer->fields->keyBy('form_field_id'),
                'user' => $formAnswer->user,
            ];
        });

        $formFields = $this->form
            ->fields()
            ->whereNotIn('type', FormField::READONLY_TYPES)
            ->get();

        return [
            'answers' => $formAnswers,
            'formFields' => $formFields,
            'showEmails' => $this->showEmails,
            'showPersonalData' => $this->showPersonalData,
        ];
    }
}
