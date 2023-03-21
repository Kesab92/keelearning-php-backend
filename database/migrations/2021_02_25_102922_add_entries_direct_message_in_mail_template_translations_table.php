<?php

use App\Models\MailTemplate;
use App\Models\MailTemplateTranslation;
use Illuminate\Database\Migrations\Migration;

class AddEntriesDirectMessageInMailTemplateTranslationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $mailTemplate = MailTemplate::where('type', '=', 'DirectMessage')->first();

        $mailTemplateTranslation = new MailTemplateTranslation();
        $mailTemplateTranslation->mail_template_id = $mailTemplate->id;
        $mailTemplateTranslation->language='de';
        $mailTemplateTranslation->title = '%appname% – Ein Administrator hat eine Nachricht für dich';
        $mailTemplateTranslation->body = "%message%


Bitte antworte nicht auf diese E-Mail, da es sich um eine vom System generierte E-Mail handelt.
Support: %contact-mail% | %contact-phone%";
        $mailTemplateTranslation->save();

        $mailTemplateTranslation = new MailTemplateTranslation();
        $mailTemplateTranslation->mail_template_id = $mailTemplate->id;
        $mailTemplateTranslation->language='de_formal';
        $mailTemplateTranslation->title = '%appname% – Ein Administrator hat eine Nachricht für Sie';
        $mailTemplateTranslation->body = "%message%


Bitte antworten Sie nicht auf diese E-Mail, da es sich um eine vom System generierte E-Mail handelt.
Support: %contact-mail% | %contact-phone%";
        $mailTemplateTranslation->save();

        $mailTemplateTranslation = new MailTemplateTranslation();
        $mailTemplateTranslation->mail_template_id = $mailTemplate->id;
        $mailTemplateTranslation->language='en';
        $mailTemplateTranslation->title = '%appname% – You have a message from an administrator';
        $mailTemplateTranslation->body = "%message%


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
        $mailTemplate = MailTemplate::where('type', '=', 'DirectMessage')->first();
        MailTemplateTranslation::where('mail_template_id' ,'=', $mailTemplate->id)->delete();
    }
}
