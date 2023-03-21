<?php

namespace App\Removers;

use App\Models\CertificateTemplate;
use App\Models\Reminder;
use App\Models\ReminderMetadata;
use App\Models\TestQuestion;
use App\Models\TestSubmission;
use App\Models\TestSubmissionAnswer;

class TestRemover extends Remover
{
    /**
     * Deletes/Resets everything depending on the category.
     *
     * @throws \Exception
     */
    protected function deleteDependees()
    {
        $this->object->certificateTemplates()->delete();
        $this->object->testCategories()->delete();
        $this->object->testQuestions()->delete();

        $reminderIds = Reminder::where('foreign_id', $this->object->id)
            ->where('app_id', appId())
            ->pluck('id');

        ReminderMetadata::whereIn('reminder_id', $reminderIds)->delete();
        Reminder::whereIn('id', $reminderIds)->delete();

        $submissionKeys = $this->object->submissions->map(function ($item) {
            return $item->id;
        });
        TestSubmissionAnswer::whereIn('test_submission_id', $submissionKeys)->delete();
        $this->object->submissions()->delete();
    }

    /**
     * Executes the actual deletion.
     *
     * @return true
     * @throws \Exception
     */
    protected function doDeletion()
    {
        $this->deleteDependees();
        $this->object->delete();

        return true;
    }

    /**
     * Gets amount of dependees that will be deleted/altered.
     *
     * @return array if clear of blocking dependees, array of strings if not
     * @throws \Exception
     */
    public function getDependees()
    {
        $id = $this->object->id;

        $submissionKeys = $this->object->submissions->map(function ($item) {
            return $item['id'];
        });

        return [
            'certificateTemplate' => CertificateTemplate::where('test_id', $id)->count(),
            'testQuestions' => TestQuestion::where('test_id', $id)->count(),
            'submissions' => TestSubmission::where('test_id', $id)->count(),
            'submissionAnswers' => TestSubmissionAnswer::whereIn('test_submission_id', $submissionKeys)->count(),
            'reminders' => Reminder::where('foreign_id', $id)->where('app_id', appId())->count(),
        ];
    }
}
