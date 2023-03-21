<?php

use App\Models\MailTemplate;
use App\Models\MailTemplateTranslation;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class FixCourseResultReminderTemplates extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $courseResultReminderTemplateIds = MailTemplate
            ::where('type', 'CourseResultReminder')
            ->pluck('id');
        $templateTranslations = MailTemplateTranslation
            ::whereIn('mail_template_id', $courseResultReminderTemplateIds)
            ->get();

        foreach ($templateTranslations as $translation) {
            $body = str_replace(
                ['%course-template-link%', 'Kursvorlage:', 'Course template:'],
                ['%course-link%', 'Kurs:', 'Course:'],
                $translation->body);
            $translation->body = $body;
            $translation->save();
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $courseResultReminderTemplateIds = MailTemplate
            ::where('type', 'CourseResultReminder')
            ->pluck('id');
        $templateTranslations = MailTemplateTranslation
            ::whereIn('mail_template_id', $courseResultReminderTemplateIds)
            ->get();

        foreach ($templateTranslations as $translation) {
            $body = str_replace(
                ['%course-link%', 'Kurs:', 'Course:'],
                ['%course-template-link%', 'Kursvorlage:', 'Course template:'],
                $translation->body);
            $translation->body = $body;
            $translation->save();
        }
    }
}
