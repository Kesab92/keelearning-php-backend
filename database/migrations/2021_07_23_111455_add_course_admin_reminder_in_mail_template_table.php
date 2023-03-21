<?php

use App\Models\MailTemplate;
use App\Models\MailTemplateTranslation;
use Illuminate\Database\Migrations\Migration;

class AddCourseAdminReminderInMailTemplateTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $mailTemplate = new MailTemplate();
        $mailTemplate->app_id = 0;
        $mailTemplate->type = 'CourseResultReminder';
        $mailTemplate->save();

        $mailTemplateTranslation = new MailTemplateTranslation();
        $mailTemplateTranslation->mail_template_id = $mailTemplate->id;
        $mailTemplateTranslation->language = 'de';
        $mailTemplateTranslation->title = 'Eskalationsmanagement – Kurs – %coursename%';
        $mailTemplateTranslation->body = "Hallo,

im Anhang finden Sie den aktuellen Stand des %mandatory-or-optional% Kurses '%coursename%' (Enddatum: %course-end%).

Kursvorlage: %course-template-link%

Dein %appname% Team


Bitte antworte nicht auf diese E-Mail, da es sich um eine vom System generierte E-Mail handelt.
Support: %contact-mail% | %contact-phone%

Teilnahme- und Nutzungsbedingungen:
%tos%";
        $mailTemplateTranslation->save();

        $mailTemplateTranslation = new MailTemplateTranslation();
        $mailTemplateTranslation->mail_template_id = $mailTemplate->id;
        $mailTemplateTranslation->language = 'en';
        $mailTemplateTranslation->title = 'Escalation Management – Course – %coursename%';
        $mailTemplateTranslation->body = "Hello,

The attachment of this message shows the current state of the %mandatory-or-optional% course '%coursename%' (end date: %course-end%)

Course template: %course-template-link%

Your %appname% Team


Please do not reply to this system generated email.
Support: %contact-mail% | %contact-phone%

Terms and conditions of participation and use:
%tos%";
        $mailTemplateTranslation->save();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $mailTemplate = MailTemplate::where('type', 'CourseResultReminder')->first();
        MailTemplateTranslation::where('mail_template_id' ,'=', $mailTemplate->id)->delete();
        $mailTemplate->delete();
    }
}
