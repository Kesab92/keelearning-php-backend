<?php

namespace App\Console\Commands;

use App\Models\App;
use App\Models\Question;
use App\Models\QuestionAnswer;
use Illuminate\Console\Command;
use Sabre\Xml\Reader;

class ImportXML extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'questions:importxml {file}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import questions from xml';

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
        $reader = new Reader();
        if ($this->argument('file') === '2tp2') {
            $filename = 'DVAUGAufgabenpool2TP2.xml';
        } elseif ($this->argument('file') === '3tp3') {
            $filename = 'DVAUGAufgabenpool3TP3.xml';
        } else {
            throw new \Exception('Invalid file');
        }
        $reader->xml(file_get_contents(base_path($filename)));
        $result = $reader->parse();
        $questions = [];
        $errors = [];
        foreach ($result['value'] as $res) {
            $type = $this->getPart($res, '{}Aufgabentyp');
            if ($type == 'Mehrfachwahl') {
                $q = $this->handleMulti($res, $errors);
                if ($q) {
                    $questions[] = $q;
                }
                continue;
            }
            if ($type == 'Einfachwahl') {
                $q = $this->handleSingle($res, $errors);
                if ($q) {
                    $questions[] = $q;
                }
                continue;
            }
            if ($type == 'Rechnen') {
                $q = $this->handleCalculate($res, $errors);
                if ($q) {
                    $questions[] = $q;
                }
                continue;
            }
            throw new \Exception('Unknown type '.$type);
        }

        $this->saveQuestions($questions);

        /*
        $data = [];
        foreach($questions as $q) {
            $count = count($q['correct']) + count($q['wrong']);
            if(!isset($data[$count])) {
                $data[$count] = [];
            }
            $data[$count][] = $q['question'];
        }
        foreach($data as $c=>$q) {
            echo $c . ' Antworten (insgesamt ' . count($q) . ' Fragen)';
            echo "\n";
            echo implode("\n", $q);
            echo "\n";
            echo "\n";
        }
         **/
    }

    private function saveQuestions($questions)
    {
        $appId = App::ID_WUERTTEMBERGISCHE;
        Db::beginTransaction();
        try {
            foreach ($questions as $q) {
                if (count($q['correct']) !== 1 && $q['type'] == 'single') {
                    throw new \Exception('Not one answer for single question');
                }

                $dbQuestion = new Question();
                $dbQuestion->app_id = $appId;
                $dbQuestion->category_id = $q['category_id'];
                $dbQuestion->visible = 1;
                if ($q['type'] == 'single') {
                    $dbQuestion->type = Question::TYPE_SINGLE_CHOICE;
                } else {
                    $dbQuestion->type = Question::TYPE_MULTIPLE_CHOICE;
                }
                $dbQuestion->title = $q['question'];
                $dbQuestion->save();

                foreach ($q['correct'] as $answer) {
                    $dbAnswer = new QuestionAnswer();
                    $dbAnswer->question_id = $dbQuestion->id;
                    $dbAnswer->content = $answer;
                    $dbAnswer->correct = true;
                    if (isset($q['note'])) {
                        $dbAnswer['feedback'] = $q['note'];
                    }
                    $dbAnswer->save();
                }

                foreach ($q['wrong'] as $answer) {
                    $dbAnswer = new QuestionAnswer();
                    $dbAnswer->question_id = $dbQuestion->id;
                    $dbAnswer->content = $answer;
                    $dbAnswer->correct = false;
                    if (isset($q['note'])) {
                        $dbAnswer['feedback'] = $q['note'];
                    }
                    $dbAnswer->save();
                }
            }
            Db::commit();
        } catch (\Exception $e) {
            Db::rollBack();
            throw $e;
        }
    }

    private function handleMulti($res, &$errors)
    {
        $correct = [];
        $wrong = [];
        $correctIds = explode(',', str_replace(' ', '', $this->getPart($res, '{}Frage_Lösung')));
        $answers = explode("\n", $this->getPart($res, '{}Frage_Antwortteil'));
        foreach ($answers as $answer) {
            $answer = utrim($answer);
            // Trim the "1.", "2.", etc
            $answerContent = utrim(substr($answer, strpos($answer, '.') + 1));
            if (in_array(explode('. ', $answer)[0], $correctIds)) {
                $correct[] = $answerContent;
            } else {
                $wrong[] = $answerContent;
            }
        }
        $question = $this->getPart($res, '{}Frage_Fragenteil');
        $situation = $this->getPart($res, '{}Frage_Situation');
        if ($situation) {
            $question = $situation.' '.$question;
        }
        if (! $correct) {
            $errors[] = 'Mehrfach Frage '.$question.' ist in ungültigem Format';

            return null;
            // throw new \Exception('No correct answer for ' . $question);
        }
        if (! $wrong) {
            $errors[] = 'Mehrfach Frage '.$question.' ist in ungültigem Format';

            return null;
            // throw new \Exception('No wrong answer for ' . $question);
        }

        $data = [
            'type' => 'multi',
            'question' => $question,
            'correct' => $correct,
            'wrong' => $wrong,
        ];

        return $this->addCategory($res, $data);
    }

    private function handleCalculate($res, &$errors)
    {
        $data = $this->handleSingle($res, $errors);
        $expl = explode("\n", $this->getPart($res, '{}Frage_Lösung'));
        unset($expl[0]);
        $note = utrim(implode("\n", $expl));
        $data['note'] = $note;

        return $data;
    }

    private function handleSingle($res, &$errors)
    {
        $correct = [];
        $wrong = [];
        $correctAnswer = utrim(explode("\n", $this->getPart($res, '{}Frage_Lösung'))[0]);
        $answers = explode("\n", $this->getPart($res, '{}Frage_Antwortteil'));
        $question = $this->getPart($res, '{}Frage_Fragenteil');
        $situation = $this->getPart($res, '{}Frage_Situation');
        if ($situation) {
            $question = $situation.' '.$question;
        }
        foreach ($answers as $answer) {
            $answer = utrim($answer);
            $expl = explode(' ', $answer);
            $num = $expl[0];
            unset($expl[0]);
            $answer = implode(' ', $expl);
            if ($num === $correctAnswer) {
                $correct[] = $answer;
            } else {
                $wrong[] = $answer;
            }
        }

        if (count($correct) !== 1) {
            $errors[] = 'Einzel Frage '.$question.' ist in ungültigem Format';

            return null;
        }
        if (! $wrong) {
            $errors[] = 'Einzel Frage '.$question.' ist in ungültigem Format';

            return null;
        }

        $data = [
            'type' => 'single',
            'question' => $question,
            'correct'  => $correct,
            'wrong'    => $wrong,
        ];

        return $this->addCategory($res, $data);
    }

    private function getPart($data, $key)
    {
        foreach ($data['value'] as $entry) {
            if ($entry['name'] === $key) {
                return $entry['value'];
            }
        }

        return null;
    }

    private function addCategory($data, $question)
    {
        if ($this->argument('file') === '2tp2') {
            $categoryId = 90;
        } elseif ($this->argument('file') === '3tp3') {
            if ($this->getPart($data, '{}HauptSachgebiet') === '3.2 Kreditprodukte') {
                $categoryId = 89;
            } else {
                $categoryId = 91;
            }
        } else {
            throw new \Exception('Invalid file');
        }
        $question['category_id'] = $categoryId;

        return $question;
    }
}
