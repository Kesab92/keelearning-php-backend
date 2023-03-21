<?php

use App\Models\MailTemplate;
use App\Models\MailTemplateTranslation;
use Illuminate\Database\Migrations\Migration;

class AddNewCourseNotificationInMailTemplateTable extends Migration
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
        $mailTemplate->type = 'NewCourseNotification';
        $mailTemplate->save();

        $mailTemplateTranslation = new MailTemplateTranslation();
        $mailTemplateTranslation->mail_template_id = $mailTemplate->id;
        $mailTemplateTranslation->language = 'de';
        $mailTemplateTranslation->title = "Neuer Kurs - %coursename%";
        $mailTemplateTranslation->body = "Hallo %username%,

der neue %mandatory-or-optional% Kurs '%coursename%' steht für dich zur Verfügung (%course-duration%). Schaue gleich mal rein:

Zum Kurs: %course-link%

Viel Spaß damit,

Dein %appname% Team


Bitte antworte nicht auf diese E-Mail, da es sich um eine vom System generierte E-Mail handelt.
Support: %contact-mail% | %contact-phone%

Teilnahme- und Nutzungsbedingungen:
%tos%";
        $mailTemplateTranslation->save();

        $mailTemplateTranslation = new MailTemplateTranslation();
        $mailTemplateTranslation->mail_template_id = $mailTemplate->id;
        $mailTemplateTranslation->language = 'en';
        $mailTemplateTranslation->title = "New course - %coursename%";
        $mailTemplateTranslation->body = "Hello %username%,

Check out the new %mandatory-or-optional% course '%coursename%' (%course-duration%). Have a look:

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
        $mailTemplate = MailTemplate::where('type', 'NewCourseNotification')->first();
        MailTemplateTranslation::where('mail_template_id' ,'=', $mailTemplate->id)->delete();
        $mailTemplate->delete();
    }
}
