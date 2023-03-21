<?php

namespace App\Console\Commands;

use App\Models\MailTemplate;
use App\Models\MailTemplateTranslation;
use Illuminate\Console\Command;
use League\Csv\Writer;

class ExportMailTranslations extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'mails:exporttranslations';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Export the translations for mail templates';

    const DEFAULT_LANGUAGE = 'de';
    const TRANSLATED_LANGUAGES = [
        'al',
        'bg',
        'cs',
        'de',
        'de_formal',
        'en',
        'es',
        'fr',
        'hr',
        'it',
        'jp',
        'nl',
        'no',
        'pl',
        'pt',
        'ro',
        'ru',
        'hr',
        'hu',
        'sr',
        'tr',
        'zh',
    ];

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
        $mailTemplates = MailTemplate::where('app_id', 0)->get();
        $mailTemplateTranslations = MailTemplateTranslation::whereIn('mail_template_id', $mailTemplates->pluck('id'))
            ->get();
        $headers = [
            'mail_template_type',
            'language',
            'title_' . self::DEFAULT_LANGUAGE,
            'body_' . self::DEFAULT_LANGUAGE,
            'title',
            'body',
        ];
        foreach (self::TRANSLATED_LANGUAGES as $targetLanguage) {
            $this->line('Exporting for ' . $targetLanguage);
            $this->output->progressStart($mailTemplates->count());
            $rows = [];
            $rows[] = $headers;
            foreach ($mailTemplates as $mailTemplate) {
                $row = [];
                $row[] = $mailTemplate->type;
                $row[] = $targetLanguage;
                $defaultTranslation = $mailTemplateTranslations->where('mail_template_id', $mailTemplate->id)
                    ->where('language', self::DEFAULT_LANGUAGE)
                    ->first();
                $row[] = $defaultTranslation->title;
                $row[] = $defaultTranslation->body;
                $targetTranslation = $mailTemplateTranslations->where('mail_template_id', $mailTemplate->id)
                    ->where('language', $targetLanguage)
                    ->first();
                if (!$targetTranslation) {
                    array_push($row, '', '');
                } else {
                    $row[] = $targetTranslation->title;
                    $row[] = $targetTranslation->body;
                }
                $rows[] = $row;
                $this->output->progressAdvance();
            }
            $this->output->progressFinish();

            if($rows > 1) {
                $filename = 'mail_templates_' . $targetLanguage . '_' . time() . '.csv';
                $filepath = storage_path('export/' . $filename);
                $writer = Writer::createFromPath($filepath, 'w+');
                $writer->insertAll($rows);
                $this->line('Export saved to '. $filepath);
            }
        }
    }
}
