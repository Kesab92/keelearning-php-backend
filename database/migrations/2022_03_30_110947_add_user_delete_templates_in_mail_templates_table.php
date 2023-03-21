<?php

use App\Models\MailTemplate;
use App\Models\MailTemplateTranslation;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddUserDeleteTemplatesInMailTemplatesTable extends Migration
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
        $mailTemplate->type = 'UserDeletionRequest';
        $mailTemplate->save();

        $mailTemplateTranslation = new MailTemplateTranslation();
        $mailTemplateTranslation->mail_template_id = $mailTemplate->id;
        $mailTemplateTranslation->language = 'de';
        $mailTemplateTranslation->title = "Account Löschaufforderung – %username%";
        $mailTemplateTranslation->body = "Der Benutzer %username% (ID %user-id%) bittet darum, dass sein Account gelöscht wird:
%user-profile-link%

Private Message:
%user-send-message-link%


Bitte antworte nicht auf diese E-Mail, da es sich um eine vom System generierte E-Mail handelt.
Support: %contact-mail% | %contact-phone%";
        $mailTemplateTranslation->save();

        $mailTemplateTranslation = new MailTemplateTranslation();
        $mailTemplateTranslation->mail_template_id = $mailTemplate->id;
        $mailTemplateTranslation->language = 'de_formal';
        $mailTemplateTranslation->title = "Account Löschaufforderung – %username%";
        $mailTemplateTranslation->body = "Der Benutzer %username% (ID %user-id%) bittet darum, dass sein Account gelöscht wird:
%user-profile-link%

Private Message:
%user-send-message-link%


Bitte antworten Sie nicht auf diese E-Mail, da es sich um eine vom System generierte E-Mail handelt.
Support: %contact-mail% | %contact-phone%";
        $mailTemplateTranslation->save();

        $mailTemplateTranslation = new MailTemplateTranslation();
        $mailTemplateTranslation->mail_template_id = $mailTemplate->id;
        $mailTemplateTranslation->language = 'en';
        $mailTemplateTranslation->title = "Account Deletion Request – %username%";
        $mailTemplateTranslation->body = "The user %username% (ID %user-id%) requests account deletion:
%user-profile-link%

Private Message:
%user-send-message-link%


Please do not reply to this system generated email.
Support: %contact-mail% | %contact-phone%";
        $mailTemplateTranslation->save();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $mailTemplate = MailTemplate::where('type', 'UserDeletionRequest')->first();
        MailTemplateTranslation::where('mail_template_id', '=', $mailTemplate->id)->delete();
        $mailTemplate->delete();
    }
}
