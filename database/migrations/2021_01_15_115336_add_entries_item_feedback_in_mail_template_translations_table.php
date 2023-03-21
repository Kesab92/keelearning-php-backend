<?php

use App\Models\MailTemplate;
use App\Models\MailTemplateTranslation;
use Illuminate\Database\Migrations\Migration;

class AddEntriesItemFeedbackInMailTemplateTranslationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $mailTemplate = MailTemplate::where('type', '=', 'ItemFeedback')->first();

        $mailTemplateTranslation = new MailTemplateTranslation();
        $mailTemplateTranslation->mail_template_id = $mailTemplate->id;
        $mailTemplateTranslation->language='de';
        $mailTemplateTranslation->title = '%feedback-type% – %username% hat Feedback zu einem Inhalt';
        $mailTemplateTranslation->body = "%username% hat folgenden Inhalt gemeldet:
%feedback-link%

Nachricht:
%feedback-message%


Bitte antworte nicht auf diese E-Mail, da es sich um eine vom System generierte E-Mail handelt.
Support: %contact-mail% | %contact-phone%";
        $mailTemplateTranslation->save();

        $mailTemplateTranslation = new MailTemplateTranslation();
        $mailTemplateTranslation->mail_template_id = $mailTemplate->id;
        $mailTemplateTranslation->language='en';
        $mailTemplateTranslation->title = '%feedback-type% – %username% remarked some content';
        $mailTemplateTranslation->body = "%username% reported the following content:
%feedback-link%

Message:
%feedback-message%


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
        $mailTemplate = MailTemplate::where('type', '=', 'ItemFeedback')->first();
        MailTemplateTranslation::where('mail_template_id' ,'=', $mailTemplate->id)->delete();
    }
}
