<?php

namespace App\Console\Commands;

use App\Models\App;
use App\Models\MailTemplate;
use Illuminate\Console\Command;

class SetupMissingDefaultMailTemplates extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'mails:setupMissingDefaultTemplates';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Creates missing default mail templates';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $baseTemplates = MailTemplate::where('app_id', 0)->get();
        $baseTemplateTypes = [
            'AppInvitation',
            'AppQuestionSuggestion',
            'AppReminder',
            'AuthResetPassword',
            'AuthWelcome',
            'CompetitionReminder',
            'CompetitionResult',
            'GameAbort',
            'GameInvitation',
            'GameReminder',
            'QuizTeamAdd',
        ];
        foreach ($baseTemplateTypes as $type) {
            if (! $baseTemplates->where('type', $type)->count()) {
                $template = new MailTemplate();
                $template->app_id = 0;
                $template->type = $type;
                $template->title = 'Title for '.$type;
                $template->body = 'Body for '.$type;
                $template->save();
            }
        }
    }
}
