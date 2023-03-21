<?php

use App\Models\MailTemplate;
use App\Models\MailTemplateTranslation;
use Illuminate\Database\Migrations\Migration;

class AddAppointmentTemplatesInMailTemplatesTable extends Migration
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
        $mailTemplate->type = 'NewAppointment';
        $mailTemplate->save();

        $mailTemplateTranslation = new MailTemplateTranslation();
        $mailTemplateTranslation->mail_template_id = $mailTemplate->id;
        $mailTemplateTranslation->language = 'de';
        $mailTemplateTranslation->title = "Neuer Termin - %appointment-name%";
        $mailTemplateTranslation->body = "Hallo %username%,

du wurdest zu dem %appointment-type% '%appointment-name%' hinzugefügt.

Termindetails: %appointment-link%


%appointment-details%


Dein %appname% Team


Bitte antworte nicht auf diese E-Mail, da es sich um eine vom System generierte E-Mail handelt.
Support: %contact-mail% | %contact-phone%

Teilnahme- und Nutzungsbedingungen:
%tos%";
        $mailTemplateTranslation->save();

        $mailTemplateTranslation = new MailTemplateTranslation();
        $mailTemplateTranslation->mail_template_id = $mailTemplate->id;
        $mailTemplateTranslation->language = 'en';
        $mailTemplateTranslation->title = "New Appointment - %appointment-name%";
        $mailTemplateTranslation->body = "Hallo %username%,

you have been added to the %appointment-type% '%appointment-name%'.

Appointment details: %appointment-link%


%appointment-details%


Your %appname% Team


Please do not reply to this system generated email.
Support: %contact-mail% | %contact-phone%

Terms and conditions of participation and use:
%tos%";
        $mailTemplateTranslation->save();

        $mailTemplate = new MailTemplate();
        $mailTemplate->app_id = 0;
        $mailTemplate->type = 'AppointmentStartDateWasUpdated';
        $mailTemplate->save();

        $mailTemplateTranslation = new MailTemplateTranslation();
        $mailTemplateTranslation->mail_template_id = $mailTemplate->id;
        $mailTemplateTranslation->language = 'de';
        $mailTemplateTranslation->title = "Der %appointment-type% '%appointment-name%' wurde %appointment-change-kind%";
        $mailTemplateTranslation->body = "Hallo %username%,

dein %appointment-type% '%appointment-name%' am %appointment-start-date% wurde %appointment-change-kind%.

Termindetails: %appointment-link%

Dein %appname% Team


Bitte antworte nicht auf diese E-Mail, da es sich um eine vom System generierte E-Mail handelt.
Support: %contact-mail% | %contact-phone%

Teilnahme- und Nutzungsbedingungen:
%tos%";
        $mailTemplateTranslation->save();

        $mailTemplateTranslation = new MailTemplateTranslation();
        $mailTemplateTranslation->mail_template_id = $mailTemplate->id;
        $mailTemplateTranslation->language = 'en';
        $mailTemplateTranslation->title = "Your %appointment-type% '%appointment-name%' has been %appointment-change-kind%";
        $mailTemplateTranslation->body = "Hallo %username%,

your %appointment-type% '%appointment-name%' on the %appointment-start-date% has been %appointment-change-kind%.

Appointment details: %appointment-link%

Your %appname% Team


Please do not reply to this system generated email.
Support: %contact-mail% | %contact-phone%

Terms and conditions of participation and use:
%tos%";
        $mailTemplateTranslation->save();

        $mailTemplate = new MailTemplate();
        $mailTemplate->app_id = 0;
        $mailTemplate->type = 'AppointmentReminder';
        $mailTemplate->save();

        $mailTemplateTranslation = new MailTemplateTranslation();
        $mailTemplateTranslation->mail_template_id = $mailTemplate->id;
        $mailTemplateTranslation->language = 'de';
        $mailTemplateTranslation->title = "Terminerinnerung - %appointment-name%";
        $mailTemplateTranslation->body = "Hallo %username%,

am %appointment-date% findet um %appointment-time% der %appointment-type% '%appointment-name%' statt.

Ort: %appointment-location%

%appointment-description%


Bitte antworte nicht auf diese E-Mail, da es sich um eine vom System generierte E-Mail handelt.
Support: %contact-mail% | %contact-phone%

Teilnahme- und Nutzungsbedingungen:
%tos%";
        $mailTemplateTranslation->save();

        $mailTemplateTranslation = new MailTemplateTranslation();
        $mailTemplateTranslation->mail_template_id = $mailTemplate->id;
        $mailTemplateTranslation->language = 'en';
        $mailTemplateTranslation->title = "Meeting - %appointment-name%";
        $mailTemplateTranslation->body = "Hello %username%,

The %appointment-type% '%appointment-name%' starts at %appointment-time% on %appointment-date%.

Location: %appointment-location%

%appointment-description%


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
        $mailTemplate = MailTemplate::where('type', 'NewAppointment')->first();
        MailTemplateTranslation::where('mail_template_id', '=', $mailTemplate->id)->delete();
        $mailTemplate->delete();

        $mailTemplate = MailTemplate::where('type', 'AppointmentStartDateWasUpdated')->first();
        MailTemplateTranslation::where('mail_template_id', '=', $mailTemplate->id)->delete();
        $mailTemplate->delete();

        $mailTemplate = MailTemplate::where('type', 'AppointmentReminder')->first();
        MailTemplateTranslation::where('mail_template_id', '=', $mailTemplate->id)->delete();
        $mailTemplate->delete();
    }
}
