<?php

namespace App\Console\Commands;

use App\Models\MailTemplate;
use App\Models\MailTemplateTranslation;
use Illuminate\Console\Command;
use League\Csv\Reader;

class ImportMailTranslations extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'mails:importtranslations {file : path to csv file}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import the translations for mail templates';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $csv = Reader::createFromPath($this->argument('file'), 'r');
        $csv->setHeaderOffset(0);
        $translations = $csv->getRecords();
        $ii = 1;
        foreach ($translations as $translation) {
            $ii += 1;
            if (
                !$translation['mail_template_type']
                || !$translation['language']
                || !$translation['title']
                || !$translation['body']
            ) {
                $this->error('Row #' . $ii . ' contains empty data.');
                continue;
            }
            $mailTemplate = MailTemplate::where('app_id', 0)
                ->where('type', $translation['mail_template_type'])
                ->first();
            if (!$mailTemplate) {
                $this->error('Could not find mail template "' . $translation['mail_template_type'] . '"');
                continue;
            }
            $mailTemplateTranslation = MailTemplateTranslation::where('mail_template_id', $mailTemplate->id)
                ->where('language', $translation['language'])
                ->first();
            if (!$mailTemplateTranslation) {
                $mailTemplateTranslation = new MailTemplateTranslation;
                $mailTemplateTranslation->mail_template_id = $mailTemplate->id;
                $mailTemplateTranslation->language = $translation['language'];
            }
            $mailTemplateTranslation->title = $translation['title'];
            $mailTemplateTranslation->body = $translation['body'];
            $mailTemplateTranslation->save();
        }
        $this->line('Successfully imported mail translations');
    }
}
