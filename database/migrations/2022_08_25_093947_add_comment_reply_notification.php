<?php

use App\Models\MailTemplate;
use App\Models\MailTemplateTranslation;
use Illuminate\Database\Migrations\Migration;

class AddCommentReplyNotification extends Migration
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
        $mailTemplate->type = 'CommentReply';
        $mailTemplate->save();

        $mailTemplateTranslation = new MailTemplateTranslation();
        $mailTemplateTranslation->mail_template_id = $mailTemplate->id;
        $mailTemplateTranslation->language = 'de';
        $mailTemplateTranslation->title = "[%content-name%] - %subcomment-author-name% hat einen Kommentar gepostet";
        $mailTemplateTranslation->body = "Hallo %realname-or-username%,

%subcomment-author-name% hat einen neuen Kommentar gepostet:
%subcomment-text%


Zur Diskussion: %content-link%

Dein %appname% Team


Bitte antworte nicht auf diese E-Mail, da es sich um eine vom System generierte E-Mail handelt.
Support: %contact-mail% | %contact-phone%

Teilnahme- und Nutzungsbedingungen:
%tos%";
        $mailTemplateTranslation->save();

        $mailTemplateTranslation = new MailTemplateTranslation();
        $mailTemplateTranslation->mail_template_id = $mailTemplate->id;
        $mailTemplateTranslation->language = 'de_formal';
        $mailTemplateTranslation->title = "[%content-name%] - %subcomment-author-name% hat einen Kommentar gepostet";
        $mailTemplateTranslation->body = "Hallo %realname-or-username%,

%subcomment-author-name% hat einen neuen Kommentar gepostet:
%subcomment-text%


Zur Diskussion: %content-link%

Dein %appname% Team


Bitte antworte nicht auf diese E-Mail, da es sich um eine vom System generierte E-Mail handelt.
Support: %contact-mail% | %contact-phone%

Teilnahme- und Nutzungsbedingungen:
%tos%";
        $mailTemplateTranslation->save();

        $mailTemplateTranslation = new MailTemplateTranslation();
        $mailTemplateTranslation->mail_template_id = $mailTemplate->id;
        $mailTemplateTranslation->language = 'en';
        $mailTemplateTranslation->title = "[%content-name%] - %subcomment-author-name% has posted a comment";
        $mailTemplateTranslation->body = "Hallo %realname-or-username%,

%subcomment-author-name% has posted a new comment:
%subcomment-text%


Discussion: %content-link%

Your %appname% Team


Please do not reply to this system generated email.
Support: %contact-mail% | %contact-phone%

Terms of service:
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
        $mailTemplate = MailTemplate::where('type', 'CommentReply')->first();
        MailTemplateTranslation::where('mail_template_id', '=', $mailTemplate->id)->delete();
        $mailTemplate->delete();
    }
}
