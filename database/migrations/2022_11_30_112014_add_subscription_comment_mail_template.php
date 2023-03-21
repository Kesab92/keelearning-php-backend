<?php

use App\Models\MailTemplate;
use App\Models\MailTemplateTranslation;
use Illuminate\Database\Migrations\Migration;

class AddSubscriptionCommentMailTemplate extends Migration
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
        $mailTemplate->type = 'SubscriptionComment';
        $mailTemplate->save();

        $mailTemplateTranslation = new MailTemplateTranslation();
        $mailTemplateTranslation->mail_template_id = $mailTemplate->id;
        $mailTemplateTranslation->language = 'de';
        $mailTemplateTranslation->title = "[%content-title%] - %comment-author% hat einen Kommentar geschrieben";
        $mailTemplateTranslation->body = "Hallo %realname-or-username%,

%comment-author% hat einen neuen Kommentar geschrieben:
%comment-text%


Zur Diskussion: %comment-link%

Dein %appname% Team


Bitte antworte nicht auf diese E-Mail, da es sich um eine vom System generierte E-Mail handelt.
Support: %contact-mail% | %contact-phone%

Teilnahme- und Nutzungsbedingungen:
%tos%


Bitte klicke auf diesen Link, falls du nicht mehr über neue Kommentare zu diesem Inhalt benachrichtigt werden möchtest:
%unsubscribe-link%";
        $mailTemplateTranslation->save();

        $mailTemplateTranslation = new MailTemplateTranslation();
        $mailTemplateTranslation->mail_template_id = $mailTemplate->id;
        $mailTemplateTranslation->language = 'de_formal';
        $mailTemplateTranslation->title = "[%content-title%] - %comment-author% hat einen Kommentar geschrieben";
        $mailTemplateTranslation->body = "Hallo %realname-or-username%,

%subcomment-author-name% hat einen neuen Kommentar gepostet:
%subcomment-text%


Zur Diskussion: %content-link%

Ihr %appname% Team


Bitte antworten Sie nicht auf diese E-Mail, da es sich um eine vom System generierte E-Mail handelt.
Support: %contact-mail% | %contact-phone%

Teilnahme- und Nutzungsbedingungen:
%tos%


Bitte klicken Sie auf diesen Link, falls Sie nicht mehr über neue Kommentare zu diesem Inhalt benachrichtigt werden möchten:
%unsubscribe-link%";
        $mailTemplateTranslation->save();

        $mailTemplateTranslation = new MailTemplateTranslation();
        $mailTemplateTranslation->mail_template_id = $mailTemplate->id;
        $mailTemplateTranslation->language = 'en';
        $mailTemplateTranslation->title = "[%content-title%] - %comment-author% has written a comment";
        $mailTemplateTranslation->body = "Hallo %realname-or-username%,

%comment-author% has written a new comment:
%comment-text%


Discussion: %comment-link%

Your %appname% Team


Please do not reply to this system generated email.
Support: %contact-mail% | %contact-phone%

Terms of service:
%tos%


Please click on this link if you want to unsubscribe from this discussion:
%unsubscribe-link%";
        $mailTemplateTranslation->save();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $mailTemplate = MailTemplate::where('type', 'SubscriptionComment')->first();
        MailTemplateTranslation::where('mail_template_id', '=', $mailTemplate->id)->delete();
        $mailTemplate->delete();
    }
}
