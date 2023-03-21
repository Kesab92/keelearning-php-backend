<?php
namespace App\Transformers\BackendApi\CourseStatistics;

use App\Transformers\AbstractTransformer;

class FormAnswerTransformer extends AbstractTransformer
{
    private bool $showPersonalData;
    private bool $showEmails;

    /**
     * @param bool $showPersonalData
     * @param bool $showEmails
     */
    public function __construct(bool $showPersonalData, bool $showEmails)
    {
        $this->showPersonalData = $showPersonalData;
        $this->showEmails = $showEmails;
    }

    public function transform($model)
    {
        if (!$model) {
            return;
        }

        $formAnswerFieldTransformer = app(FormAnswerFieldTransformer::class);
        $fields = $model->fields->sortBy(function($field) {
            return $field->formField->position;
        })->values();
        $user = [];

        if($this->showPersonalData) {
            $user['username'] = $model->user->username;
        }
        if($this->showEmails) {
            $user['email'] = $model->user->email;
        }

        return [
            'fields' => $formAnswerFieldTransformer->transformAll($fields),
            'user' => $user,
        ];
    }
}

