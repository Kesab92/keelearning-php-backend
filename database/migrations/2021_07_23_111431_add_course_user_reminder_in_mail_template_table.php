<?php

use App\Models\MailTemplate;
use App\Models\MailTemplateTranslation;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

class AddCourseUserReminderInMailTemplateTable extends Migration
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
        $mailTemplate->type = 'CourseReminder';
        $mailTemplate->save();

        $mailTemplateTranslation = new MailTemplateTranslation();
        $mailTemplateTranslation->mail_template_id = $mailTemplate->id;
        $mailTemplateTranslation->language = 'de';
        $mailTemplateTranslation->title = 'Vergiss nicht den Kurs \'%coursename%\' zu beenden';
        $mailTemplateTranslation->body = "Hallo %username%,

vergiss nicht den %mandatory-or-optional% Kurs '%coursename%' zu beenden. Dieser ist bis zum %active_until% aktiv.

Zum Kurs: %course-link%

Dein %appname% Team


Bitte antworte nicht auf diese E-Mail, da es sich um eine vom System generierte E-Mail handelt.
Support: %contact-mail% | %contact-phone%

Teilnahme- und Nutzungsbedingungen:
%tos%";
        $mailTemplateTranslation->save();

        $mailTemplateTranslation = new MailTemplateTranslation();
        $mailTemplateTranslation->mail_template_id = $mailTemplate->id;
        $mailTemplateTranslation->language = 'en';
        $mailTemplateTranslation->title = 'Don\'t forget to finish the course \'%coursename%\'';
        $mailTemplateTranslation->body = "Hello %username%,

Don't forget to finish the %mandatory-or-optional% course '%coursename%'. It is only active until %active_until%.

Course link: %course-link%

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
        $mailTemplate = MailTemplate::where('type', 'CourseReminder')->first();
        MailTemplateTranslation::where('mail_template_id' ,'=', $mailTemplate->id)->delete();
        $mailTemplate->delete();
    }
}
