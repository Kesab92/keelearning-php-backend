<?php

namespace App\Console\Commands;

use App\Models\Question;
use Illuminate\Console\Command;
use League\Csv\Writer;

class ExportQuestions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'export:questions {appid : app to export}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Exports all active questions as CSV';

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
     * @return mixed
     */
    public function handle()
    {
        $this->appid = $this->argument('appid');
        $export = [];
        $export[] = [
        'questionId', 'questionTitle', 'questionType',
        'visible', 'categoryName',
        //'answerXId', 'answerXContent', 'answerXFeedback', 'answerXCorrect',
        ];

        $questions = Question::whereAppId($this->appid)->get();
        $this->line('Exporting '.iterator_count($questions).' Questions...');
        $bar = $this->output->createProgressBar(iterator_count($questions));
        $answerCount = 0;
        foreach ($questions as $question) {
            $answerCount = max($answerCount, $question->questionAnswers()->count());
            $bar->advance();
        }
        $bar->finish();
        $this->line('');

        $i = 1;
        while ($i <= $answerCount) {
            $export[0][] = 'answer'.$i.'Id';
            $export[0][] = 'answer'.$i.'Content';
            $export[0][] = 'answer'.$i.'Feedback';
            $export[0][] = 'answer'.$i.'Correct';
            $i++;
        }

        $bar = $this->output->createProgressBar(iterator_count($questions));
        foreach ($questions as $question) {
            $answers = $question->questionAnswers()
                            ->get();
            switch ($question->type) {
                case Question::TYPE_BOOLEAN:
                    $questionType = 'BOOLEAN';
                    break;
                case Question::TYPE_SINGLE_CHOICE:
                    $questionType = 'SINGLE_CHOICE';
                    break;
                case Question::TYPE_MULTIPLE_CHOICE:
                    $questionType = 'MULTIPLE_CHOICE';
                    break;
                case Question::TYPE_INDEX_CARD:
                    $questionType = 'INDEX_CARD';
                    break;
                default:
                    $questionType = '';
                    break;
            }
            $entry = [
            $question->id, $question->title, $questionType,
            $question->visible, $question->category()->first() ? $question->category()->first()->name : null,
            ];
            $i = 0;
            foreach ($answers as $answer) {
                $entry[] = $answer->id;
                $entry[] = $answer->content;
                $entry[] = $answer->feedback;
                $entry[] = $answer->correct;
                $i++;
            }
            while ($i < $answerCount) {
                $entry[] = '';
                $entry[] = '';
                $entry[] = '';
                $entry[] = '';
                $i++;
            }
            $export[] = $entry;
            $bar->advance();
        }
        $bar->finish();
        $this->line('');

        $filename = $this->appid.'_questions_'.time().'.csv';
        $filepath = storage_path('export/'.$filename);

        $writer = Writer::createFromPath($filepath, 'w+');
        $writer->insertAll($export);

        $this->info('Export saved to '.$filepath);
    }
}
