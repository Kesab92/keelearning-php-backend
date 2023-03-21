<?php

use App\Models\MailTemplate;
use App\Models\MailTemplateTranslation;
use Illuminate\Database\Migrations\Migration;

class AddCoursePreviewRequestMailTemplate extends Migration
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
        $mailTemplate->type = 'CourseAccessRequest';
        $mailTemplate->save();

        $mailTemplateTranslation = new MailTemplateTranslation();
        $mailTemplateTranslation->mail_template_id = $mailTemplate->id;
        $mailTemplateTranslation->language='de';
        $mailTemplateTranslation->title = '%username% erbittet Zugriff auf einen Inhalt';
        $mailTemplateTranslation->body = "%user-link% hätte gerne Zugriff auf:
%course-link%

Um %user-link% Zugriff auf den Inhalt zu gewähren, weise dem Benutzer einen der folgenden TAGs zu:
%tag-list%


Bitte antworte nicht auf diese E-Mail, da es sich um eine vom System generierte E-Mail handelt.
Support: %contact-mail% | %contact-phone%";
        $mailTemplateTranslation->save();

        $mailTemplateTranslation = new MailTemplateTranslation();
        $mailTemplateTranslation->mail_template_id = $mailTemplate->id;
        $mailTemplateTranslation->language='en';
        $mailTemplateTranslation->title = '%username% requests access to content';
        $mailTemplateTranslation->body = "%user-link% would like to get access to:
%course-link%

To allow %user-link% to access the content, assign the user one of the following TAGs:
%tag-list%

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
        $mailTemplates = MailTemplate::where('type', 'CourseAccessRequest');
        foreach($mailTemplates as $mailTemplate) {
            $mailTemplate->deleteAllTranslations();
        }
        $mailTemplates->delete();
    }
}
